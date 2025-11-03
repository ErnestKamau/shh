<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentAmendment;
use App\Services\DMS\AmendmentWorkflowService;
use App\Services\DMS\PermissionResolver;

class AmendmentManager extends Component
{
    use WithPagination, WithFileUploads;

    public $showModal = false;
    public $showWorkflowModal = false;
    public $currentAmendment = null;
    public $file;
    
    public $amendmentForm = [
        'document_id' => null,
        'amendment_reason' => '',
        'amendment_description' => '',
    ];

    public $workflowAction = '';
    public $workflowComment = '';

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    
    public $message = '';
    public $messageType = 'success';

    public $documents = [];
    public $perPageOptions = [10, 25, 50, 100];

    protected $workflowService;
    protected $permissionResolver;

    public function boot(AmendmentWorkflowService $workflowService, PermissionResolver $permissionResolver)
    {
        $this->workflowService = $workflowService;
        $this->permissionResolver = $permissionResolver;
    }

    public function mount(): void
    {
        $this->documents = Document::active()->orderBy('title')->get();
    }

    public function getAmendmentsProperty()
    {
        $query = DocumentAmendment::with(['document.documentType', 'requester', 'authorizer', 'approver']);

        if ($this->search) {
            $query->whereHas('document', function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('document_number', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        $amendments = $query->orderBy('created_at', 'desc')->paginate($this->perPage);
        
        // Add status_class to each amendment to avoid PHP logic in Blade
        $amendments->getCollection()->transform(function($amendment) {
            $amendment->status_class = match($amendment->status) {
                'approved' => 'success',
                'completed' => 'info',
                'rejected' => 'danger',
                'requested' => 'warning',
                default => 'secondary'
            };
            return $amendment;
        });
        
        return $amendments;
    }

    public function showRequestModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function requestAmendment(): void
    {
        $this->validate([
            'amendmentForm.document_id' => 'required|exists:documents,id',
            'amendmentForm.amendment_reason' => 'required|string',
            'amendmentForm.amendment_description' => 'nullable|string',
        ]);

        try {
            $document = Document::findOrFail($this->amendmentForm['document_id']);

            $amendment = $this->workflowService->processAmendmentRequest(
                $document,
                $this->amendmentForm['amendment_reason'],
                $this->amendmentForm['amendment_description'],
                auth()->user()
            );

            $this->message = 'Amendment request submitted successfully';
            $this->messageType = 'success';
            $this->closeModal();

        } catch (\Exception $e) {
            $this->message = 'Error requesting amendment: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function showWorkflow($amendmentId): void
    {
        $this->currentAmendment = DocumentAmendment::with(['document'])->findOrFail($amendmentId);
        $this->showWorkflowModal = true;
    }

    public function authorizeAmendment($approved): void
    {
        try {
            $this->workflowService->executeWorkflowStep(
                $this->currentAmendment,
                'authorization',
                auth()->user(),
                $approved,
                $this->workflowComment
            );

            $this->message = 'Amendment ' . ($approved ? 'authorized' : 'rejected') . ' successfully';
            $this->messageType = 'success';
            $this->closeWorkflowModal();

        } catch (\Exception $e) {
            $this->message = 'Error processing amendment: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function uploadAmendedFile(): void
    {
        $this->validate([
            'file' => 'required|file|max:51200',
        ]);

        try {
            $this->workflowService->uploadAmendedFile($this->currentAmendment, $this->file);

            $this->message = 'Amended file uploaded successfully';
            $this->messageType = 'success';
            $this->file = null;
            $this->closeWorkflowModal();

        } catch (\Exception $e) {
            $this->message = 'Error uploading file: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function approveAmendment($approved): void
    {
        try {
            $this->workflowService->executeWorkflowStep(
                $this->currentAmendment,
                'approval',
                auth()->user(),
                $approved,
                $this->workflowComment
            );

            $this->message = 'Amendment ' . ($approved ? 'approved' : 'rejected') . ' successfully';
            $this->messageType = 'success';
            $this->closeWorkflowModal();

        } catch (\Exception $e) {
            $this->message = 'Error processing amendment: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeWorkflowModal(): void
    {
        $this->showWorkflowModal = false;
        $this->currentAmendment = null;
        $this->workflowComment = '';
        $this->file = null;
    }

    public function resetForm(): void
    {
        $this->amendmentForm = [
            'document_id' => null,
            'amendment_reason' => '',
            'amendment_description' => '',
        ];
        $this->resetValidation();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
    }

    public function render()
    {
        return view('livewire.dms.amendment-manager-component', [
            'amendments' => $this->amendments,
        ]);
    }
}

