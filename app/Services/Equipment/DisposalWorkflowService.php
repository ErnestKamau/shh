<?php

namespace App\Services\Equipment;

use App\User;
use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\EquipmentDisposalApproval;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflow;
use App\Models\Equipments\EquipmentDisposalApprovalWorkflowStep;
use App\Services\Equipment\DisposalAuditService;
use App\Services\Equipment\DisposalNotificationService;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;

class DisposalWorkflowService
{
    protected $auditService;
    protected $notificationService;

    public function __construct(
        DisposalAuditService $auditService,
        DisposalNotificationService $notificationService
    ) {
        $this->auditService = $auditService;
        $this->notificationService = $notificationService;
    }

    /**
     * Load workflow for equipment based on type, location, and company
     *
     * @param EquipmentDisposal $disposal
     * @return EquipmentDisposalApprovalWorkflow|null
     */
    public function loadWorkflowForDisposal(EquipmentDisposal $disposal): ?EquipmentDisposalApprovalWorkflow
    {
        $equipment = $disposal->equipment;
        $equipmentTypeId = $equipment->asset_type_id ?? null;
        $locationId = $equipment->asset_location_id ?? null;
        $companyId = $disposal->company_id;

        return EquipmentDisposalApprovalWorkflow::findForEquipment(
            $equipmentTypeId,
            $locationId,
            $companyId
        );
    }

    /**
     * Initialize approval workflow for disposal request
     *
     * @param EquipmentDisposal $disposal
     * @return array
     */
    public function initializeWorkflow(EquipmentDisposal $disposal): array
    {
        $workflow = $this->loadWorkflowForDisposal($disposal);

        if (!$workflow) {
            return [
                'success' => false,
                'message' => 'No approval workflow found for this equipment configuration.',
            ];
        }

        $steps = $workflow->getActiveSteps();

        if ($steps->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Workflow has no approval steps configured.',
            ];
        }

        // Check if requester is assigned as approver
        foreach ($steps as $step) {
            if ($this->isUserAssignee($step, $disposal->requested_by)) {
                return [
                    'success' => false,
                    'message' => 'Requester cannot be an approver in the workflow.',
                ];
            }
        }

        // Create approval records for each step
        foreach ($steps as $step) {
            EquipmentDisposalApproval::create([
                'disposal_id' => $disposal->id,
                'step' => $step->step_order,
                'approver_id' => $this->getAssigneeUserId($step),
            ]);
        }

        // Update disposal status to pending
        $oldStatus = $disposal->status;
        $disposal->status = 'pending';
        $disposal->save();

        // Log workflow initialization
        $this->auditService->logAction(
            $disposal,
            'submitted',
            ['status' => $oldStatus],
            ['status' => 'pending'],
            "Disposal request submitted and workflow initialized with {$steps->count()} approval step(s)."
        );

        return [
            'success' => true,
            'message' => 'Workflow initialized successfully.',
            'workflow' => $workflow,
            'steps' => $steps,
        ];
    }

    /**
     * Get the current approval step for a disposal
     *
     * @param EquipmentDisposal $disposal
     * @return EquipmentDisposalApproval|null
     */
    public function getCurrentApprovalStep(EquipmentDisposal $disposal): ?EquipmentDisposalApproval
    {
        return $disposal->approvals()
            ->whereNull('decision')
            ->orderBy('step')
            ->first();
    }

    /**
     * Check if user can approve the current step
     *
     * @param EquipmentDisposal $disposal
     * @param User $user
     * @return bool
     */
    public function canUserApproveCurrentStep(EquipmentDisposal $disposal, User $user): bool
    {
        $currentStep = $this->getCurrentApprovalStep($disposal);

        if (!$currentStep) {
            return false;
        }

        // Get workflow step configuration
        $workflow = $this->loadWorkflowForDisposal($disposal);
        if (!$workflow) {
            return false;
        }

        $workflowStep = $workflow->steps()
            ->where('step_order', $currentStep->step)
            ->first();

        if (!$workflowStep) {
            return false;
        }

        return $this->canUserApproveStep($workflowStep, $user);
    }

    /**
     * Check if user can approve a workflow step
     *
     * @param EquipmentDisposalApprovalWorkflowStep $step
     * @param User $user
     * @return bool
     */
    public function canUserApproveStep(EquipmentDisposalApprovalWorkflowStep $step, User $user): bool
    {
        return $step->canUserApprove($user);
    }

    /**
     * Process approval decision
     *
     * @param EquipmentDisposal $disposal
     * @param User $approver
     * @param string $decision
     * @param string|null $remarks
     * @param string|null $signaturePath
     * @return array
     */
    public function processApproval(
        EquipmentDisposal $disposal,
        User $approver,
        string $decision,
        ?string $remarks = null,
        ?string $signaturePath = null
    ): array {
        if (!in_array($decision, ['approve', 'reject'])) {
            return [
                'success' => false,
                'message' => 'Invalid decision. Must be "approve" or "reject".',
            ];
        }

        // Mandatory remarks for rejection
        if ($decision === 'reject' && empty($remarks)) {
            return [
                'success' => false,
                'message' => 'Remarks are mandatory when rejecting a disposal request.',
            ];
        }

        $currentStep = $this->getCurrentApprovalStep($disposal);

        if (!$currentStep) {
            return [
                'success' => false,
                'message' => 'No pending approval step found.',
            ];
        }

        // Verify approver is assigned to this step
        if ($currentStep->approver_id !== $approver->id) {
            return [
                'success' => false,
                'message' => 'You are not authorized to approve this step.',
            ];
        }

        DB::beginTransaction();

        try {
            // Update approval record
            $currentStep->decision = $decision;
            $currentStep->remarks = $remarks;
            $currentStep->signature_path = $signaturePath;
            $currentStep->decided_at = now();
            $currentStep->save();

            $oldStatus = $disposal->status;

            if ($decision === 'reject') {
                // Rejection stops the workflow
                $disposal->status = 'rejected';
                $disposal->save();

                $this->auditService->logAction(
                    $disposal,
                    'rejected',
                    ['status' => $oldStatus],
                    ['status' => 'rejected'],
                    "Disposal request rejected at step {$currentStep->step} by {$approver->name}. Remarks: {$remarks}"
                );

                DB::commit();

                // Send decision notification
                $this->notificationService->notifyApprovalDecision($disposal, 'rejected');

                return [
                    'success' => true,
                    'message' => 'Disposal request rejected successfully.',
                    'status' => 'rejected',
                ];
            }

            // Check if there are more approval steps
            $nextStep = $disposal->approvals()
                ->whereNull('decision')
                ->where('step', '>', $currentStep->step)
                ->orderBy('step')
                ->first();

            if (!$nextStep) {
                // All approvals complete
                $disposal->status = 'approved';
                $disposal->save();

                $this->auditService->logAction(
                    $disposal,
                    'approved',
                    ['status' => $oldStatus],
                    ['status' => 'approved'],
                    "Disposal request approved by {$approver->name} at step {$currentStep->step}. All approvals complete."
                );

                // Notify about approval completion and decommissioning requirement
                $this->notificationService->notifyApprovalDecision($disposal, 'approved');
                $this->notificationService->notifyDecommissioningRequired($disposal);
            } else {
                // More steps pending
                $this->auditService->logAction(
                    $disposal,
                    'approved',
                    ['status' => $oldStatus],
                    ['status' => $disposal->status],
                    "Disposal request approved by {$approver->name} at step {$currentStep->step}. Waiting for next approval."
                );

                // Notify next approver if there is one
                if ($nextStep->approver_id) {
                    $nextApprover = User::find($nextStep->approver_id);
                    if ($nextApprover) {
                        $this->notificationService->notifyApprovalRequired($disposal, $nextApprover);
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => $nextStep ? 'Approval recorded. Waiting for next step.' : 'All approvals complete.',
                'status' => $disposal->status,
                'hasNextStep' => $nextStep !== null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error processing approval: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check if user is assigned to a workflow step
     *
     * @param EquipmentDisposalApprovalWorkflowStep $step
     * @param int $userId
     * @return bool
     */
    protected function isUserAssignee(EquipmentDisposalApprovalWorkflowStep $step, int $userId): bool
    {
        if ($step->assignee_type === User::class && $step->assignee_id === $userId) {
            return true;
        }

        if (in_array($step->assignee_type, ['App\\Models\\Role', 'App\\Role', SpatieRole::class], true)) {
            $user = User::find($userId);
            if ($user) {
                return $user->roles->pluck('id')->contains($step->assignee_id);
            }
        }

        return false;
    }

    /**
     * Get assignee user ID from workflow step
     * For role-based assignments, returns null (multiple users possible)
     *
     * @param EquipmentDisposalApprovalWorkflowStep $step
     * @return int|null
     */
    protected function getAssigneeUserId(EquipmentDisposalApprovalWorkflowStep $step): ?int
    {
        if ($step->assignee_type === User::class) {
            return $step->assignee_id;
        }

        // For role-based, we'll need to assign to a specific user when they take action
        // For now, we can assign to the first user with that role, or leave null
        // The system will need to handle this when processing approvals
        return null;
    }
}

