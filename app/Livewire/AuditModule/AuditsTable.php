<?php

namespace App\Livewire\AuditModule;

use App\Models\AuditModule\Audit;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class AuditsTable extends Component
{
    use WithPagination;

    public $status = 'All Audit';
    public $search = '';
    public $perPage = 15;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $showFilters = false;
    
    public $filters = [
        'audit_type_id' => '',
        'date_from' => '',
        'date_to' => '',
        'lead_auditor_id' => '',
    ];

    public $showDeleteModal = false;
    public $auditToDelete = null;
    public $showStatusModal = false;
    public $auditToChangeStatus = null;
    public $newStatus = '';
    public $statusNotes = '';
    public $availableWorkflowSteps = [];
    public $currentWorkflowStep = null;
    
    // Bulk selection and workflow actions
    public $selectedAudits = [];
    public $selectAll = false;
    public $showWorkflowActionModal = false;
    public $workflowActionAuditId = null;
    public $workflowAction = '';
    public $workflowTargetStatusId = '';
    public $workflowRemarks = '';
    public $availableStatuses = [];
    public $availableWorkflowActions = [];
    public $selectedWorkflowActionRule = null;
    public $isBulkAction = false;
    
    protected function getListeners()
    {
        return [
            'refresh' => '$refresh',
        ];
    }
    
    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'All Audit'],
        'perPage' => ['except' => 15],
    ];

    public function mount($status = 'All Audit')
    {
        $this->status = $status;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->filters = [
            'audit_type_id' => '',
            'date_from' => '',
            'date_to' => '',
            'lead_auditor_id' => '',
        ];
        $this->search = '';
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->auditToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function deleteAudit()
    {
        if ($this->auditToDelete) {
            $audit = Audit::find($this->auditToDelete);
            if ($audit) {
                $audit->delete();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Audit deleted successfully.']);
            }
        }
        $this->showDeleteModal = false;
        $this->auditToDelete = null;
    }

    public function openStatusModal($id)
    {
        $this->auditToChangeStatus = $id;
        $audit = Audit::find($id);
        
        // Prevent status changes for closed audits
        if ($audit->status_name === 'Closed') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot change status. Audit is closed and finalized.']);
            return;
        }
        
        // Get current workflow step
        $this->currentWorkflowStep = $audit->getCurrentWorkflowStep() ?? 1;
        $workflowSteps = getAuditWorkflowSteps();
        
        // Get available steps: current step, previous steps (backwards), and next step
        // Note: Step 1 "All Audit" is a filter name, but the actual status is "Scheduled"
        $this->availableWorkflowSteps = [];
        
        // Map step 1 to "Scheduled" for the modal (not "All Audit")
        $stepDisplayNames = $workflowSteps;
        $stepDisplayNames[1] = 'Scheduled'; // Use "Scheduled" in modal instead of "All Audit"
        
        // If current step is 1, show step 1 (Scheduled) and step 2
        if ($this->currentWorkflowStep == 1) {
            $this->availableWorkflowSteps[1] = $stepDisplayNames[1]; // Scheduled
            $this->availableWorkflowSteps[2] = $stepDisplayNames[2];
            $this->newStatus = $stepDisplayNames[1]; // Default to current (Scheduled)
        } else {
            // Add all previous steps (backwards - can go back), including step 1
            for ($i = 1; $i <= $this->currentWorkflowStep; $i++) {
                $this->availableWorkflowSteps[$i] = $stepDisplayNames[$i];
            }
            
            // Add next step if exists (forward - can proceed)
            if ($this->currentWorkflowStep < 8) {
                $nextStep = $this->currentWorkflowStep + 1;
                $this->availableWorkflowSteps[$nextStep] = $stepDisplayNames[$nextStep];
            }
            
            // Set default to current step
            $this->newStatus = $stepDisplayNames[$this->currentWorkflowStep] ?? 'Record Findings';
        }
        $this->statusNotes = '';
        $this->showStatusModal = true;
    }

    public function changeStatus()
    {
        if ($this->auditToChangeStatus && $this->newStatus) {
            $audit = Audit::find($this->auditToChangeStatus);
            if ($audit) {
                // Prevent status changes for closed audits
                if ($audit->status_name === 'Closed') {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot change status. Audit is closed and finalized.']);
                    return;
                }
                
                $oldStatus = $audit->status_name;
                $workflowSteps = getAuditWorkflowSteps();
                
                // Get the target workflow step number from the selected step name
                // Handle both "Scheduled" (modal display for step 1) and "All Audit" (filter name)
                $targetStepNum = null;
                if ($this->newStatus === 'Scheduled' || $this->newStatus === 'All Audit') {
                    $targetStepNum = 1;
                } else {
                    $targetStepNum = array_search($this->newStatus, $workflowSteps);
                }
                
                if (!$targetStepNum) {
                    $this->dispatch('notify', ['type' => 'error', 'message' => 'Invalid workflow step selected.']);
                    return;
                }
                
                // Validate workflow progression (only for forward movement)
                if ($targetStepNum > $this->currentWorkflowStep) {
                    // Moving forward - validate progression
                    if (!$audit->canProceedToStep($targetStepNum)) {
                        // Provide specific error messages based on what's missing
                        $errorMessage = "Cannot proceed to '{$this->newStatus}'. ";
                        if ($this->currentWorkflowStep === 2 && $audit->findings()->count() === 0) {
                            $errorMessage .= "Please record at least one finding before proceeding.";
                        } elseif ($targetStepNum === 5) {
                            // Check for missing RCAs
                            $ncs = $audit->nonConformances()->get();
                            $missingRcas = [];
                            foreach ($ncs as $nc) {
                                if (!$nc->hasRca()) {
                                    $missingRcas[] = $nc->nc_number;
                                }
                            }
                            if (!empty($missingRcas)) {
                                $errorMessage .= "The following NCs need Root Cause Analysis: " . implode(', ', $missingRcas) . ".";
                            } else {
                                $errorMessage .= "Please complete the required steps in order.";
                            }
                        } elseif ($targetStepNum === 8) {
                            // Check which CAPAs are not verified (only check if verification record exists)
                            $unverifiedCapas = [];
                            foreach ($audit->nonConformances as $nc) {
                                foreach ($nc->correctiveActions as $capa) {
                                    if (!$capa->hasVerification()) {
                                        $unverifiedCapas[] = $capa->capa_number . ' (no verification record)';
                                    }
                                }
                            }
                            if (!empty($unverifiedCapas)) {
                                $errorMessage .= "The following corrective actions must be verified: " . implode(', ', $unverifiedCapas) . ".";
                            } else {
                                $errorMessage .= "All corrective actions must be verified.";
                            }
                        } else {
                            $errorMessage .= "Please complete the required steps in order.";
                        }
                        $this->dispatch('notify', ['type' => 'error', 'message' => $errorMessage]);
                        return;
                    }
                }
                // Moving backwards or staying at current step - allowed
                
                // Store the workflow step name directly in status_name
                // Exception: Step 1 uses "Scheduled" instead of "All Audit" (which is just a filter)
                // Step 8 uses "Closed" instead of "Close"
                if ($targetStepNum === 1) {
                    $newStatusName = 'Scheduled';
                } elseif ($targetStepNum === 8) {
                    $newStatusName = 'Closed';
                } else {
                    $newStatusName = $this->newStatus; // Use the workflow step name as the status
                }
                
                // Special handling for step 8 (Close)
                if ($targetStepNum === 8 && !$audit->canProceedToStep(8)) {
                    // Check which CAPAs are not verified (only check if verification record exists)
                    $unverifiedCapas = [];
                    foreach ($audit->nonConformances as $nc) {
                        foreach ($nc->correctiveActions as $capa) {
                            if (!$capa->hasVerification()) {
                                $unverifiedCapas[] = $capa->capa_number . ' (no verification record)';
                            }
                        }
                    }
                    if (!empty($unverifiedCapas)) {
                        $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot close audit. The following corrective actions must be verified: ' . implode(', ', $unverifiedCapas) . '.']);
                    } else {
                        $this->dispatch('notify', ['type' => 'error', 'message' => 'Cannot close audit. All corrective actions must be verified.']);
                    }
                    return;
                }
                
                // For step 3, warn if no NCs exist (but allow the change)
                if ($targetStepNum === 3 && $audit->nonConformances()->count() === 0) {
                    // Allow the change, but the workflow step calculation will still show step 2 until NCs are created
                    // This is expected - the workflow step is calculated from data
                }
                
                // Get the status record to update both status_id and status_name
                $targetStatus = \App\Models\AuditModule\AuditStatus::where('name', $newStatusName)
                    ->forCompany()
                    ->first();
                
                if ($targetStatus) {
                    // Update both status_id and status_name to keep them in sync
                    $audit->status_id = $targetStatus->id;
                    $audit->status_name = $newStatusName;
                } else {
                    // Fallback: just update status_name if status record not found
                    $audit->status_name = $newStatusName;
                }
                
                // Ensure audit has required data for the target step
                if ($targetStepNum >= 2 && !$audit->start_date) {
                    $audit->start_date = now();
                }
                
                if ($targetStepNum === 8) {
                    $audit->closure_date = now();
                    $audit->closed_by = auth()->id();
                }
                
                $audit->updated_by = auth()->id();
                $audit->save();
                
                \App\Models\AuditModule\AuditActivityLog::logStatusChange($audit, $oldStatus, $newStatusName, $this->statusNotes);
                
                $this->dispatch('notify', ['type' => 'success', 'message' => "Audit workflow step changed to {$this->newStatus}."]);
            }
        }
        $this->showStatusModal = false;
        $this->auditToChangeStatus = null;
        $this->newStatus = '';
        $this->statusNotes = '';
        $this->availableWorkflowSteps = [];
        $this->currentWorkflowStep = null;
    }

    public function updatedSelectAll($value)
    {
        $this->toggleSelectAll();
    }
    
    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedAudits = $this->getAuditIds()->toArray();
        } else {
            $this->selectedAudits = [];
        }
    }

    public function updatedSelectedAudits()
    {
        // Ensure selectedAudits is always an array
        if (!is_array($this->selectedAudits)) {
            $this->selectedAudits = [];
            return;
        }
        
        // Don't modify the array - just update selectAll checkbox state
        // Livewire handles the array binding automatically for checkboxes
        $allIds = $this->getAuditIds()->toArray();
        $selectedCount = is_array($this->selectedAudits) ? count(array_filter($this->selectedAudits)) : 0;
        $this->selectAll = !empty($allIds) && $selectedCount > 0 && $selectedCount === count($allIds);
    }

    public function getAuditIds()
    {
        $query = Audit::forCompany();
        
        if ($this->status !== 'All Audit') {
            $query->where('status_name', $this->status);
        }
        
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('audit_number', 'like', $searchTerm)
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('department', 'like', $searchTerm)
                    ->orWhere('auditee_name', 'like', $searchTerm);
            });
        }
        
        if ($this->filters['audit_type_id']) {
            $query->where('audit_type_id', $this->filters['audit_type_id']);
        }
        if ($this->filters['date_from']) {
            $query->whereDate('scheduled_date', '>=', $this->filters['date_from']);
        }
        if ($this->filters['date_to']) {
            $query->whereDate('scheduled_date', '<=', $this->filters['date_to']);
        }
        if ($this->filters['lead_auditor_id']) {
            $query->where('lead_auditor_id', $this->filters['lead_auditor_id']);
        }
        
        return $query->pluck('id');
    }

    public function openWorkflowActionModal($auditId = null)
    {
        // If auditId is provided, it's a single audit action
        // If auditId is null, use selected audits (bulk action)
        $this->isBulkAction = $auditId === null;
        
        if ($this->isBulkAction) {
            // For bulk action, use selected audits
            if (empty($this->selectedAudits)) {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Please select at least one audit.']);
                return;
            }
            $this->workflowActionAuditId = null;
        } else {
            // For single audit action
            $this->workflowActionAuditId = $auditId;
        }
        
        $this->workflowAction = '';
        $this->workflowTargetStatusId = '';
        $this->workflowRemarks = '';
        $this->availableStatuses = getActiveAuditStatuses();
        
        // Get available workflow actions for the current status
        if (!$this->isBulkAction && $auditId) {
            $audit = Audit::find($auditId);
            if ($audit) {
                // Get available workflow action rules for this audit's current status
                $workflowActionRules = getAvailableWorkflowActions($audit->status_id, $audit->status_name);
                
                // Extract unique workflow actions from the rules (keep as collection, not array)
                $this->availableWorkflowActions = $workflowActionRules
                    ->pluck('workflowAction')
                    ->filter()
                    ->unique('id')
                    ->values()
                    ->all(); // Use all() instead of toArray() to preserve model objects
                
                $nextStatus = $audit->getNextWorkflowStatus();
                if ($nextStatus) {
                    $this->workflowTargetStatusId = $nextStatus->id;
                }
            } else {
                // Fallback: get all active workflow actions
                $this->availableWorkflowActions = getActiveWorkflowActions()->all();
            }
        } else {
            // For bulk actions, show all active workflow actions
            $this->availableWorkflowActions = getActiveWorkflowActions()->all();
        }
        
        // Ensure it's always an array
        if (!is_array($this->availableWorkflowActions)) {
            $this->availableWorkflowActions = [];
        }
        
        $this->showWorkflowActionModal = true;
    }
    
    public function updatedWorkflowAction()
    {
        // Reset selected rule
        $this->selectedWorkflowActionRule = null;
        $this->workflowTargetStatusId = '';
        
        if (empty($this->workflowAction)) {
            return;
        }
        
        // Find the workflow action rule based on selected action and current audit status
        if (!$this->isBulkAction && $this->workflowActionAuditId) {
            $audit = Audit::find($this->workflowActionAuditId);
            if ($audit) {
                // Get workflow action rules for this status
                $rules = getAvailableWorkflowActions($audit->status_id, $audit->status_name);
                
                // Find the rule that matches the selected action
                $this->selectedWorkflowActionRule = $rules->first(function($rule) {
                    return ($rule->workflowAction && ($rule->workflowAction->code === $this->workflowAction || $rule->workflowAction->id == $this->workflowAction));
                });
                
                // Set default target status based on rule's target_type
                if ($this->selectedWorkflowActionRule) {
                    $targetType = $this->selectedWorkflowActionRule->target_type ?? 'specific';
                    
                    switch ($targetType) {
                        case 'specific':
                            // Use the specific target status from the rule
                            if ($this->selectedWorkflowActionRule->target_status_id) {
                                $this->workflowTargetStatusId = $this->selectedWorkflowActionRule->target_status_id;
                            } elseif ($this->selectedWorkflowActionRule->target_status_name) {
                                // Fallback: find status by name
                                $targetStatus = \App\Models\AuditModule\AuditStatus::where('name', $this->selectedWorkflowActionRule->target_status_name)
                                    ->forCompany()
                                    ->first();
                                if ($targetStatus) {
                                    $this->workflowTargetStatusId = $targetStatus->id;
                                }
                            }
                            break;
                            
                        case 'next':
                            // Get the next workflow status
                            $nextStatus = $audit->getNextWorkflowStatus();
                            if ($nextStatus) {
                                $this->workflowTargetStatusId = $nextStatus->id;
                            } elseif ($this->selectedWorkflowActionRule->target_status_id) {
                                // Fallback to rule's target_status_id if getNextWorkflowStatus fails
                                $this->workflowTargetStatusId = $this->selectedWorkflowActionRule->target_status_id;
                            }
                            break;
                            
                        case 'current':
                            // Keep the current status
                            if ($audit->status_id) {
                                $this->workflowTargetStatusId = $audit->status_id;
                            }
                            break;
                            
                        case 'previous':
                            // Get the previous workflow status
                            $currentStep = $audit->getCurrentWorkflowStep();
                            if ($currentStep && $currentStep > 1) {
                                $previousStep = $currentStep - 1;
                                $previousStatus = \App\Models\AuditModule\AuditStatus::active()
                                    ->forCompany()
                                    ->where('workflow_step', $previousStep)
                                    ->ordered()
                                    ->first();
                                
                                if ($previousStatus) {
                                    $this->workflowTargetStatusId = $previousStatus->id;
                                } elseif ($this->selectedWorkflowActionRule->target_status_id) {
                                    // Fallback to rule's target_status_id
                                    $this->workflowTargetStatusId = $this->selectedWorkflowActionRule->target_status_id;
                                }
                            }
                            break;
                    }
                }
            }
        } else {
            // For bulk actions, try to find a rule (may not be perfect match)
            $workflowAction = getWorkflowActionByCode($this->workflowAction);
            if ($workflowAction) {
                // Get first available rule for this action (bulk actions may have multiple rules)
                $this->selectedWorkflowActionRule = \App\Models\AuditModule\WorkflowActionRule::forCompany()
                    ->active()
                    ->where('workflow_action_id', $workflowAction->id)
                    ->with(['workflowAction', 'targetStatus'])
                    ->first();
            }
        }
    }

    public function submitWorkflowAction()
    {
        // Get the workflow action to validate requirements
        $workflowAction = getWorkflowActionByCode($this->workflowAction);
        
        $validationRules = [
            'workflowAction' => 'required|string',
        ];
        
        $validationMessages = [
            'workflowAction.required' => 'Please select an action.',
        ];
        
        if ($workflowAction) {
            if ($workflowAction->requires_remarks) {
                $minLength = $workflowAction->min_remarks_length ?? 10;
                $validationRules['workflowRemarks'] = 'required|string|min:' . $minLength;
                $validationMessages['workflowRemarks.required'] = 'Remarks are required for ISO compliance.';
                $validationMessages['workflowRemarks.min'] = "Remarks must be at least {$minLength} characters.";
            }
            
            // Target status is now determined automatically from rules, no need for user input validation
            // But we still need to ensure it's set
            if (!$this->workflowTargetStatusId && $this->selectedWorkflowActionRule) {
                // If target status not set, try to set it from the rule
                $this->updatedWorkflowAction();
            }
        } else {
            // Fallback validation if action not found
            $validationRules['workflowTargetStatusId'] = 'required|exists:audit_statuses,id';
            $validationRules['workflowRemarks'] = 'required|string|min:10';
        }
        
        $this->validate($validationRules, $validationMessages);

        $targetStatus = null;
        if ($this->workflowTargetStatusId) {
            $targetStatus = \App\Models\AuditModule\AuditStatus::find($this->workflowTargetStatusId);
        }
        
        $auditIds = $this->isBulkAction ? $this->selectedAudits : [$this->workflowActionAuditId];
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($auditIds as $auditId) {
            $audit = Audit::find($auditId);
            if (!$audit) {
                $errorCount++;
                continue;
            }
            
            if ($audit->status_name === 'Closed') {
                $errorCount++;
                continue;
            }
            
            // Get the rule for this specific audit
            $rule = null;
            if ($workflowAction) {
                $rule = \App\Models\AuditModule\WorkflowActionRule::forCompany()
                    ->active()
                    ->where('workflow_action_id', $workflowAction->id)
                    ->where(function($q) use ($audit) {
                        $q->where('from_status_id', $audit->status_id)
                          ->orWhere('from_status_name', $audit->status_name);
                    })
                    ->first();
            }
            
            $oldStatus = $audit->status_name;
            $newStatus = $oldStatus; // Default to current status
            
            // Determine target status based on rule or selected status
            if ($rule) {
                if ($rule->target_type === 'next') {
                    $nextStatus = $audit->getNextWorkflowStatus();
                    if ($nextStatus) {
                        $newStatus = $nextStatus->name;
                        $targetStatus = $nextStatus;
                    }
                } elseif ($rule->target_type === 'specific' && $rule->target_status_id) {
                    $targetStatus = $rule->targetStatus;
                    if ($targetStatus) {
                        $newStatus = $targetStatus->name;
                    }
                } elseif ($rule->target_type === 'current') {
                    // Stay at current status
                    $newStatus = $oldStatus;
                }
            } elseif ($targetStatus) {
                // Use manually selected target status
                $newStatus = $targetStatus->name;
            }
            
            // Validate progression if rule requires it
            if ($rule && $rule->validate_progression && $rule->target_type === 'next') {
                if (!$audit->canProceedToNextStatus()) {
                    $errorCount++;
                    continue;
                }
            }
            
            // Validate workflow conditions
            if ($rule && $rule->conditions) {
                $validationService = new \App\Services\AuditModule\WorkflowValidationService();
                $validationResult = $validationService->validateWorkflowConditions($audit, $rule);
                
                if (!$validationResult['valid']) {
                    $errorMessages = implode(' ', $validationResult['errors']);
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'message' => $rule->error_message ?: "Validation failed: {$errorMessages}"
                    ]);
                    $errorCount++;
                    continue;
                }
            }
            
            // Update both status_id and status_name to keep them in sync
            if ($targetStatus) {
                $audit->status_id = $targetStatus->id;
                $audit->status_name = $newStatus;
            }
            $audit->updated_by = auth()->id();
            
            if ($newStatus === 'Closed') {
                $audit->closure_date = now();
                $audit->closed_by = auth()->id();
            }
            
            $audit->save();
            
            // Send notifications if rule has them
            if ($rule) {
                $this->sendWorkflowActionNotifications($audit, $rule, $workflowAction);
            }
            
            $actionText = $workflowAction ? $workflowAction->name : ucfirst($this->workflowAction);
            $notes = "[{$actionText}] {$this->workflowRemarks}";
            \App\Models\AuditModule\AuditActivityLog::logStatusChange($audit, $oldStatus, $newStatus, $notes);
            
            $successCount++;
        }
        
        if ($successCount > 0) {
            $message = $this->isBulkAction 
                ? "Workflow action applied to {$successCount} audit(s) successfully."
                : "Workflow action applied successfully.";
            $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
        }
        
        if ($errorCount > 0) {
            $this->dispatch('notify', ['type' => 'error', 'message' => "Could not apply action to {$errorCount} audit(s)."]);
        }
        
        $this->closeWorkflowActionModal();
    }
    
    protected function sendWorkflowActionNotifications($audit, $rule, $workflowAction)
    {
        $notifications = $rule->activeNotifications;
        
        foreach ($notifications as $notification) {
            // TODO: Implement notification sending logic
            // This would send emails/SMS based on notification configuration
        }
    }

    public function closeWorkflowActionModal()
    {
        $wasBulkAction = $this->isBulkAction;
        $this->showWorkflowActionModal = false;
        $this->workflowActionAuditId = null;
        $this->workflowAction = '';
        $this->workflowTargetStatusId = '';
        $this->workflowRemarks = '';
        $this->availableWorkflowActions = [];
        $this->availableStatuses = [];
        $this->selectedWorkflowActionRule = null;
        $this->isBulkAction = false;
        
        // Clear selections only if it was a bulk action (user might want to keep selections for other actions)
        // Actually, let's keep selections so user can do multiple bulk actions
    }

    public function getSelectedCountProperty()
    {
        if (!is_array($this->selectedAudits)) {
            return 0;
        }
        return count(array_filter($this->selectedAudits, function($val) {
            return !empty($val) && (int)$val > 0;
        }));
    }

    public function render()
    {
        $query = Audit::forCompany()
            ->with(['auditType', 'leadAuditor', 'findings']);

        // Filter by workflow step based on status name
        if ($this->status === 'All Audit') {
            // Step 1 "All Audit" shows all audits (no filter)
            // No additional filtering needed
        } else {
            // Filter by status name directly to match sidebar counts logic
            $query->where('status_name', $this->status);
        }

        // Search
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('audit_number', 'like', $searchTerm)
                    ->orWhere('title', 'like', $searchTerm)
                    ->orWhere('department', 'like', $searchTerm)
                    ->orWhere('auditee_name', 'like', $searchTerm);
            });
        }

        // Advanced filters
        if ($this->filters['audit_type_id']) {
            $query->where('audit_type_id', $this->filters['audit_type_id']);
        }
        if ($this->filters['date_from']) {
            $query->whereDate('scheduled_date', '>=', $this->filters['date_from']);
        }
        if ($this->filters['date_to']) {
            $query->whereDate('scheduled_date', '<=', $this->filters['date_to']);
        }
        if ($this->filters['lead_auditor_id']) {
            $query->where('lead_auditor_id', $this->filters['lead_auditor_id']);
        }

        // Eager load all necessary relationships for comprehensive audit details
        $eagerLoad = [
            'auditType',
            'leadAuditor',
            'findings.findingCategory',
            'findings.nonConformance',
            'checklists.items',
            'nonConformances' => function ($q) {
                $q->with([
                    'rootCauseAnalysis',
                    'correctiveActions' => function ($cq) {
                        $cq->with([
                            'latestVerification',
                            'actionOwnerUser',
                            'status',
                        ]);
                    },
                    'status',
                    'riskLevel',
                ]);
            },
            'attachments',
        ];

        if (Schema::hasTable('audit_checklist_item_responses')) {
            $eagerLoad[] = 'checklistItemResponses';
        }

        $query->with($eagerLoad);
        
        $audits = $query->orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        
        $auditTypes = getActiveAuditTypes();
        $auditors = getAuditorUsers();
        $availableStatuses = getActiveAuditStatuses();

        return view('livewire.audit-module.audits-table', [
            'audits' => $audits,
            'auditTypes' => $auditTypes,
            'auditors' => $auditors,
            'availableStatuses' => $availableStatuses,
        ]);
    }
}

