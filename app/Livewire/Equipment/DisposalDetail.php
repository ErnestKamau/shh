<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Equipments\EquipmentDisposal;
use App\Services\Equipment\DisposalWorkflowService;
use App\Services\Equipment\DisposalAuditService;
use App\Services\Equipment\DisposalReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DisposalDetail extends Component
{
    use WithFileUploads;

    public $disposalId;
    public $disposal;
    public $activeTab = 'details';

    // Modal states
    public $showApprovalModal = false;
    public $showExecutionModal = false;
    public $currentApprovalStep = null;

    // Approval form
    public $approvalForm = [
        'decision' => '',
        'remarks' => '',
        'signature' => null,
    ];

    // Execution form
    public $executionForm = [
        'final_disposal_method' => '',
        'disposal_date' => '',
        'executed_by' => null,
        'witness_id' => null,
        'compliance_checklist' => [],
    ];
    public $executionPhotos = [];
    public $executionDocuments = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $workflowService;
    protected $auditService;
    protected $reportService;

    public function boot(
        DisposalWorkflowService $workflowService,
        DisposalAuditService $auditService,
        DisposalReportService $reportService
    ) {
        $this->workflowService = $workflowService;
        $this->auditService = $auditService;
        $this->reportService = $reportService;
    }

    public function mount(string $disposalId): void
    {
        $this->disposalId = $disposalId;
        $this->loadDisposal();
    }

    public function loadDisposal(): void
    {
        $this->disposal = EquipmentDisposal::with([
            'equipment',
            'requester',
            'executor',
            'witness',
            'approvals.approver',
            'files',
            'auditLogs.user',
        ])->find($this->disposalId);

        if (!$this->disposal) {
            $this->message = 'Disposal request not found.';
            $this->messageType = 'danger';
        }
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->loadDisposal(); // Refresh data when switching tabs
    }

    public function openApprovalModal(): void
    {
        if (!$this->disposal) {
            return;
        }

        $currentStep = $this->workflowService->getCurrentApprovalStep($this->disposal);
        
        if (!$currentStep) {
            $this->message = 'No pending approval step found.';
            $this->messageType = 'warning';
            return;
        }

        $this->currentApprovalStep = $currentStep;
        $this->approvalForm = [
            'decision' => '',
            'remarks' => '',
            'signature' => null,
        ];
        $this->showApprovalModal = true;
    }

    public function closeApprovalModal(): void
    {
        $this->showApprovalModal = false;
        $this->currentApprovalStep = null;
        $this->approvalForm = [
            'decision' => '',
            'remarks' => '',
            'signature' => null,
        ];
    }

    public function processApproval(): void
    {
        $this->validate([
            'approvalForm.decision' => 'required|in:approve,reject',
            'approvalForm.remarks' => 'required_if:approvalForm.decision,reject|string',
            'approvalForm.signature' => 'nullable|image|max:2048',
        ]);

        $signaturePath = null;

        // Handle signature upload
        if ($this->approvalForm['signature']) {
            $path = $this->approvalForm['signature']->store('equipment-disposals/signatures', 'public');
            $signaturePath = '/storage/' . $path;
        } elseif (auth()->user()->electronic_sig) {
            // Use default signature from user profile
            $signaturePath = auth()->user()->electronic_sig;
        }

        $result = $this->workflowService->processApproval(
            $this->disposal,
            auth()->user(),
            $this->approvalForm['decision'],
            $this->approvalForm['remarks'] ?? null,
            $signaturePath
        );

        if ($result['success']) {
            $this->message = $result['message'];
            $this->messageType = 'success';
            $this->closeApprovalModal();
            $this->loadDisposal();
            $this->dispatch('approval-processed');
        } else {
            $this->message = $result['message'];
            $this->messageType = 'danger';
        }
    }

    public function openExecutionModal(): void
    {
        if (!$this->disposal || $this->disposal->status !== 'approved') {
            $this->message = 'Disposal must be approved before execution.';
            $this->messageType = 'warning';
            return;
        }

        if (!Gate::forUser(auth()->user())->allows('update', $this->disposal)) {
            $this->message = 'You do not have permission to execute this disposal.';
            $this->messageType = 'danger';
            return;
        }

        $this->executionForm = [
            'final_disposal_method' => $this->disposal->proposed_method ?? '',
            'disposal_date' => date('Y-m-d'),
            'executed_by' => auth()->id(),
            'witness_id' => null,
            'compliance_checklist' => $this->getDefaultChecklist(),
        ];
        $this->executionPhotos = [];
        $this->executionDocuments = [];
        $this->showExecutionModal = true;
    }

    public function closeExecutionModal(): void
    {
        $this->showExecutionModal = false;
        $this->executionForm = [
            'final_disposal_method' => '',
            'disposal_date' => '',
            'executed_by' => null,
            'witness_id' => null,
            'compliance_checklist' => [],
        ];
        $this->executionPhotos = [];
        $this->executionDocuments = [];
    }

    public function executeDisposal(): void
    {
        if (!Gate::forUser(auth()->user())->allows('update', $this->disposal)) {
            $this->message = 'You do not have permission to execute this disposal.';
            $this->messageType = 'danger';
            return;
        }

        $this->validate([
            'executionForm.final_disposal_method' => 'required|string|max:255',
            'executionForm.disposal_date' => 'required|date',
            'executionForm.executed_by' => 'required|integer|exists:users,id',
            'executionForm.witness_id' => 'required|integer|exists:users,id|different:executionForm.executed_by',
            'executionForm.compliance_checklist' => 'required|array|min:1',
            'executionPhotos.*' => 'nullable|image|max:10240',
            'executionDocuments.*' => 'nullable|file|max:10240',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $this->disposal->status;

            // Update disposal record
            $this->disposal->final_disposal_method = $this->executionForm['final_disposal_method'];
            $this->disposal->disposal_date = $this->executionForm['disposal_date'];
            $this->disposal->executed_by = $this->executionForm['executed_by'];
            $this->disposal->witness_id = $this->executionForm['witness_id'];
            $this->disposal->compliance_checklist_json = $this->executionForm['compliance_checklist'];
            $this->disposal->status = 'executed';
            $this->disposal->save();

            // Upload execution photos
            foreach ($this->executionPhotos as $photo) {
                if ($photo) {
                    $path = $photo->store('equipment-disposals/' . $this->disposal->id . '/execution', 'public');
                    
                    \App\Models\Equipments\EquipmentDisposalFile::create([
                        'disposal_id' => $this->disposal->id,
                        'file_path' => '/storage/' . $path,
                        'file_name' => $photo->getClientOriginalName(),
                        'file_type' => 'photo',
                        'mime_type' => $photo->getMimeType(),
                        'file_size' => $photo->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // Upload execution documents
            foreach ($this->executionDocuments as $doc) {
                if ($doc) {
                    $path = $doc->store('equipment-disposals/' . $this->disposal->id . '/execution', 'public');
                    
                    \App\Models\Equipments\EquipmentDisposalFile::create([
                        'disposal_id' => $this->disposal->id,
                        'file_path' => '/storage/' . $path,
                        'file_name' => $doc->getClientOriginalName(),
                        'file_type' => 'document',
                        'mime_type' => $doc->getMimeType(),
                        'file_size' => $doc->getSize(),
                        'uploaded_by' => auth()->id(),
                    ]);
                }
            }

            // Update equipment status
            $this->disposal->equipment->update([
                'is_disposal' => 1,
                'status' => 'Disposed',
            ]);

            // Log execution
            $this->auditService->logExecution($this->disposal, [
                'final_disposal_method' => $this->disposal->final_disposal_method,
                'disposal_date' => $this->disposal->disposal_date->format('Y-m-d'),
                'executed_by' => $this->disposal->executed_by,
                'witness_id' => $this->disposal->witness_id,
            ]);

            // Generate PDF report
            try {
                $this->reportService->generateReport($this->disposal);
            } catch (\Exception $e) {
                // Log error but don't fail the execution
                \Log::error('Failed to generate disposal report: ' . $e->getMessage());
            }

            DB::commit();

            $this->message = 'Disposal executed successfully! Report generated.';
            $this->messageType = 'success';
            $this->closeExecutionModal();
            $this->loadDisposal();
            $this->dispatch('disposal-executed');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error executing disposal: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    protected function getDefaultChecklist(): array
    {
        return [
            'equipment_removed_from_service' => false,
            'equipment_labeled' => false,
            'regulatory_requirements_met' => false,
            'witness_present' => false,
            'documentation_complete' => false,
        ];
    }

    public function canApprove(): bool
    {
        if (!$this->disposal || $this->disposal->status !== 'pending') {
            return false;
        }

        return $this->workflowService->canUserApproveCurrentStep($this->disposal, auth()->user());
    }

    public function canExecute(): bool
    {
        return $this->disposal && 
               $this->disposal->status === 'approved' &&
               Gate::forUser(auth()->user())->allows('update', $this->disposal);
    }

    public function downloadReport(): void
    {
        if (!Gate::forUser(auth()->user())->allows('view', $this->disposal)) {
            $this->message = 'You do not have permission to download this report.';
            $this->messageType = 'danger';
            return;
        }

        try {
            $this->reportService->downloadReport($this->disposal);
        } catch (\Exception $e) {
            $this->message = 'Error downloading report: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function viewReport(): void
    {
        if (!Gate::forUser(auth()->user())->allows('view', $this->disposal)) {
            $this->message = 'You do not have permission to view this report.';
            $this->messageType = 'danger';
            return;
        }

        try {
            $this->reportService->streamReport($this->disposal);
        } catch (\Exception $e) {
            $this->message = 'Error viewing report: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function render()
    {
        return view('livewire.equipment.disposal-detail');
    }
}

