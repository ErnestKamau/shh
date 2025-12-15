<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\EquipmentDisposalApproval;
use App\Services\Equipment\DisposalWorkflowService;
use App\Services\Equipment\DisposalAuditService;

class DisposalApprovalModal extends Component
{
    use WithFileUploads;

    public $disposalId;
    public $disposal;
    public $approvalStep;
    public $showModal = false;

    public $approvalForm = [
        'decision' => '',
        'remarks' => '',
        'signature' => null,
    ];

    public $message = '';
    public $messageType = 'success';

    protected $workflowService;
    protected $auditService;

    protected function rules(): array
    {
        return [
            'approvalForm.decision' => 'required|in:approve,reject',
            'approvalForm.remarks' => 'required_if:approvalForm.decision,reject|string',
            'approvalForm.signature' => 'nullable|image|max:2048',
        ];
    }

    public function boot(DisposalWorkflowService $workflowService, DisposalAuditService $auditService)
    {
        $this->workflowService = $workflowService;
        $this->auditService = $auditService;
    }

    public function openModal(int $disposalId): void
    {
        $this->disposalId = $disposalId;
        $this->disposal = EquipmentDisposal::with(['equipment', 'approvals.approver'])->find($disposalId);
        
        if (!$this->disposal) {
            $this->message = 'Disposal request not found.';
            $this->messageType = 'danger';
            return;
        }

        $this->approvalStep = $this->workflowService->getCurrentApprovalStep($this->disposal);
        
        if (!$this->approvalStep) {
            $this->message = 'No pending approval step found.';
            $this->messageType = 'warning';
            return;
        }

        if (!$this->workflowService->canUserApproveCurrentStep($this->disposal, auth()->user())) {
            $this->message = 'You are not authorized to approve this step.';
            $this->messageType = 'danger';
            return;
        }

        $this->resetApprovalForm();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->disposalId = null;
        $this->disposal = null;
        $this->approvalStep = null;
        $this->resetApprovalForm();
    }

    public function resetApprovalForm(): void
    {
        $this->approvalForm = [
            'decision' => '',
            'remarks' => '',
            'signature' => null,
        ];
        $this->message = '';
        $this->messageType = 'success';
    }

    public function processApproval(): void
    {
        $this->validate();

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
            
            // Emit event and close modal
            $this->dispatch('approval-processed', ['disposal_id' => $this->disposal->id]);
            $this->closeModal();
        } else {
            $this->message = $result['message'];
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
        return view('livewire.equipment.disposal-approval-modal');
    }
}

