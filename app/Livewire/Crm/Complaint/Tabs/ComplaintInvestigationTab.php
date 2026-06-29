<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Services\CRM\ComplaintInvestigationReportService;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use App\Jobs\GenerateCloseReports;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class ComplaintInvestigationTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $resolution;

    // Sequential Flow Flags
    public $checkpoint_completed = false;

    // Stepper State
    public int $currentStep = 1;
    public $carDecision = null; // 'yes', 'no'
    public $ncDecision = null;  // 'yes', 'no'
    public array $sharedData = [];

    // Form fields
    public $cause_of_complaint;
    public $root_cause_by = [];
    public $root_cause_date;
    public $action_taken; 
    public $action_taken_date;
    public $action_taken_by = [];
    public $corrective_action_taken;
    public $corrective_action_date;
    public $corrective_action_by = [];
    // Remarks fields for Review & Close
    public $client_remarks = '';
    public $complaint_review_remarks = '';
    public $car_required = false;
    public $ncr_required = false;
    public $users = [];
    public $showCheckpoint = false;
    public int $activeStep = 1; // 1: Cause, 2: Immediate, 3: Corrective
    public bool $isEditing = false;
    public bool $hasExistingInvestigation = false;
    public string $car_decision = 'none'; // 'none', 'have_car', 'have_car_nc'
    
    public function refreshState($recalculateStep = true)
    {
        $this->complaint->refresh();
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)
            ->first();
        
        if ($this->resolution) {
            // Update all findings and dates to ensure view is current
            $this->cause_of_complaint = $this->resolution->cause_of_complaint;
            $this->root_cause_by = $this->resolution->root_cause_by ? array_map('trim', explode(',', $this->resolution->root_cause_by)) : [];
            $this->root_cause_date = $this->resolution->root_cause_date ? $this->resolution->root_cause_date->format('Y-m-d') : $this->root_cause_date;
            $this->action_taken = $this->resolution->action_taken;
            $this->action_taken_date = $this->resolution->action_taken_date ? $this->resolution->action_taken_date->format('Y-m-d') : $this->action_taken_date;
            $this->action_taken_by = $this->resolution->action_taken_by ? array_map('trim', explode(',', $this->resolution->action_taken_by)) : [];
            $this->corrective_action_taken = $this->resolution->corrective_action_taken;
            $this->corrective_action_date = $this->resolution->corrective_action_date ? $this->resolution->corrective_action_date->format('Y-m-d') : $this->corrective_action_date;
            $this->corrective_action_by = $this->resolution->corrective_action_by ? array_map('trim', explode(',', $this->resolution->corrective_action_by)) : [];
            
            $this->car_required = (bool) $this->resolution->car_required;
            $this->ncr_required = (bool) $this->resolution->ncr_required;
            
            // Map to decision labels used by Stepper logic
            if ($this->car_required) {
                $this->carDecision = 'yes';
                $this->ncDecision = $this->ncr_required ? 'yes' : 'no';
            } else {
                $this->carDecision = 'no';
                $this->ncDecision = 'no';
            }

            // Sync checkpoint status
            $this->checkpoint_completed = ($this->complaint->complaint_workflow >= 3) || 
                \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $this->complaintId)
                ->where('action', 'like', 'Classification Determined%')
                ->exists();
                
            if (!empty($this->cause_of_complaint)) {
                $this->hasExistingInvestigation = true;
            }

            // Sync remarks if they exist
            $this->client_remarks = $this->resolution->client_remarks ?? $this->client_remarks;
            $this->complaint_review_remarks = $this->resolution->internal_remarks ?? $this->complaint_review_remarks;
        } else {
            if ($this->complaint->complaint_workflow >= 3) {
                $this->checkpoint_completed = true;
            }
        }
        
        // Recalculate current step based on new logic
        if ($recalculateStep) {
            $this->calculateCurrentStep();
        }
    }
    
    #[On('checkpoint-completed')]
    public function handleCheckpointCompleted()
    {
        $this->refreshState();

        // Auto-advance to the next step (NC or CAPA) if we just completed Step 1
        if ($this->checkpoint_completed && $this->currentStep == 1) {
            $this->goToNextStep();
        }
    }

    #[On('complaint-workflow-updated')]
    public function handleWorkflowUpdated()
    {
        $this->refreshState();
    }

    #[On('trigger-next-action')]
    public function handleTriggerNextAction($action)
    {
        if ($action === 'add_findings') {
            $this->goToStep(1, true);
        } elseif ($action === 'record_decision') {
            $this->dispatch('show-decision-modal');
        } elseif ($action === 'toggle_edit') {
            $this->toggleEdit();
        }
    }
    
    public function updatedCarRequired($value)
    {
        if (!$value) {
            $this->ncr_required = false;
        }
    }

    // Modal state (Legacy - keeping for compatibility if needed elsewhere)
    public $activeModalTab = 'cause';

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::findOrFail($complaintId);
        
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();
        
        if ($this->resolution) {
            $this->cause_of_complaint = $this->resolution->cause_of_complaint;
            $this->root_cause_by = $this->resolution->root_cause_by ? array_map('trim', explode(',', $this->resolution->root_cause_by)) : [];
            $this->root_cause_date = $this->resolution->root_cause_date ? $this->resolution->root_cause_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->action_taken = $this->resolution->action_taken;
            $this->action_taken_date = $this->resolution->action_taken_date ? $this->resolution->action_taken_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->action_taken_by = $this->resolution->action_taken_by ? array_map('trim', explode(',', $this->resolution->action_taken_by)) : [];
            $this->corrective_action_taken = $this->resolution->corrective_action_taken;
            $this->corrective_action_date = $this->resolution->corrective_action_date ? $this->resolution->corrective_action_date->format('Y-m-d') : now()->format('Y-m-d');
            $this->corrective_action_by = $this->resolution->corrective_action_by ? array_map('trim', explode(',', $this->resolution->corrective_action_by)) : [];
            $this->car_required = (bool) $this->resolution->car_required;
            $this->ncr_required = (bool) $this->resolution->ncr_required;
            
            // Sequential state detection:
            // 1. Check if workflow decision is done (Stage 3+ or audit record exists)
            $this->checkpoint_completed = ($this->complaint->complaint_workflow >= 3) || 
                \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $this->complaintId)
                ->where('action', 'like', 'Classification Determined%')
                ->exists();

            // Set active step based on progress for No-CAR path
            $this->determineActiveStep();

            if (!empty($this->cause_of_complaint)) {
                $this->showCheckpoint = true;
                $this->hasExistingInvestigation = true;
            }

            // Initialize decision based on existing data
            if ($this->car_required) {
                $this->carDecision = 'yes';
                $this->ncDecision = $this->ncr_required ? 'yes' : 'no';
            } else {
                $this->carDecision = 'no';
                $this->ncDecision = 'no';
            }

            $this->calculateCurrentStep();
        } else {
            $this->action_taken_date = now()->format('Y-m-d');
            $this->corrective_action_date = now()->format('Y-m-d');
            $this->currentStep = 1;
            $this->carDecision = null;
            $this->ncDecision = null;
            $this->root_cause_date = now()->format('Y-m-d');
        }

        $this->users = getAllUsers();

        // Load remarks if any
        $resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();
        if ($resolution) {
            $this->client_remarks = $resolution->client_remarks ?? '';
            $this->complaint_review_remarks = $resolution->internal_remarks ?? '';
        }
    }

    public function calculateCurrentStep()
    {
        // Default to Cause if not recorded
        if (empty($this->cause_of_complaint)) {
            $this->currentStep = 1;
            return;
        }

        // If checkpoint not completed, we are at Step 1 (Decision pending)
        if (!$this->checkpoint_completed) {
            $this->currentStep = 1;
            return;
        }

        // Logic based on decisions
        if ($this->carDecision === 'no') {
            // No CAR Path: Cause -> Corrective Actions -> Review
            // If corrective actions are recorded, we can move to Review
            if (!empty($this->action_taken) && !empty($this->corrective_action_taken)) {
                $this->currentStep = 3;
            } elseif ($this->complaint->complaint_workflow >= 3) {
                $this->currentStep = 2;
            } else {
                $this->currentStep = 1;
            }
        } else {
            // CAR Path
            if ($this->ncDecision === 'yes') {
                // Cause -> NC -> CAPA -> Review
                if ($this->isNcCompleted()) {
                    if ($this->isCapaCompleted()) {
                        $this->currentStep = 4;
                    } else {
                        $this->currentStep = 3;
                    }
                } else {
                    $this->currentStep = 2;
                }
            } else {
                // Cause -> CAPA -> Review
                if ($this->isCapaCompleted()) {
                    $this->currentStep = 3;
                } else {
                    $this->currentStep = 2;
                }
            }
        }
    }

    protected function isNcCompleted()
    {
        $capa = \App\Models\CRM\CapaRecord::where('complaint_id', $this->complaintId)->first();
        if (!$capa) return false;
        $whys = $capa->why_why_analysis ?? [];
        if (is_string($whys)) $whys = json_decode($whys, true) ?? [];
        
        $hasProblem = !empty(strip_tags((string)($whys['problem_statement'] ?? '')));
        $hasWhys = !empty($whys['whys'][0]) || !empty($whys['why_1']);
        $hasRootCause = !empty(strip_tags((string)($whys['root_cause_analysis'] ?? '')));
        
        return $hasProblem && $hasWhys && $hasRootCause;
    }

    protected function isCapaCompleted()
    {
        $capa = \App\Models\CRM\CapaRecord::where('complaint_id', $this->complaintId)->first();
        return $capa && !empty($capa->effectiveness_verified_by) && !empty($capa->effectiveness_date);
    }

    public function goToStep($step, $editing = false)
    {
        // Enforce sequential locking: Step 2+ requires Step 1 decision (checkpoint)
        if ($step > 1 && !$this->checkpoint_completed) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Please record your findings and a resolution decision from Step 1 before proceeding.']);
            return;
        }

        // Fresh data sync before moving, but skip auto-step calculation
        // since the user is explicitly requesting a target step.
        $this->refreshState(false);
        
        $this->currentStep = $step;
        $this->isEditing = $editing;
        
        if ($editing) {
            $this->dispatch('toggle-active-step-edit', isEditing: true);
        }
    }

    public function goToNextStep()
    {
        // Enforce sequential locking: Proceeding from Step 1 requires decision (checkpoint)
        if ($this->currentStep == 1 && !$this->checkpoint_completed) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Please record a resolution decision before proceeding to the next step.']);
            return;
        }

        $this->currentStep++;
    }

    public function goToPreviousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    #[On('nc-completed')]
    public function handleNcCompleted($rootCause = null, $correctiveAction = null)
    {
        if ($rootCause || $correctiveAction) {
            $this->sharedData['root_cause'] = $rootCause;
            $this->sharedData['corrective_action'] = $correctiveAction;
        }
        $this->refreshState();
        $this->calculateCurrentStep(); // Move to CAPA
    }

    #[On('capa-completed')]
    public function handleCapaCompleted()
    {
        $this->refreshState();
        $this->calculateCurrentStep(); // Move to Review
    }

    public function setStep($step)
    {
        $this->activeStep = $step;
        $this->isEditing = true;
        
        if ($step == 2 && empty($this->action_taken_date)) {
            $this->action_taken_date = now()->format('Y-m-d');
        }
        if ($step == 3 && empty($this->corrective_action_date)) {
            $this->corrective_action_date = now()->format('Y-m-d');
        }
    }

    protected function determineActiveStep()
    {
        if ($this->checkpoint_completed && !$this->car_required) {
            if (empty($this->action_taken)) {
                $this->activeStep = 2;
            } elseif (empty($this->corrective_action_taken)) {
                $this->activeStep = 3;
            } else {
                $this->activeStep = 1; // Default to 1 if all are filled but we want to edit
            }
        } else {
            $this->activeStep = 1;
        }
    }

    public function nextStep()
    {
        if ($this->activeStep < 3) {
            $this->performDraftSave(false);
            $this->activeStep++;
            
            if ($this->activeStep == 2 && empty($this->action_taken_date)) {
                $this->action_taken_date = now()->format('Y-m-d');
            }
            if ($this->activeStep == 3 && empty($this->corrective_action_date)) {
                $this->corrective_action_date = now()->format('Y-m-d');
            }
        }
    }

    public function previousStep()
    {
        if ($this->activeStep > 1) {
            $this->activeStep--;
        }
    }

    public function saveAsDraft()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        $this->performDraftSave(true);
        $this->isEditing = false;
        $this->showSuccess('Investigation draft saved.');
    }

    protected function performDraftSave($notify = false)
    {
        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
        }

        $this->resolution->cause_of_complaint = $this->cause_of_complaint;
        $this->resolution->root_cause_by = is_array($this->root_cause_by) ? implode(', ', array_filter($this->root_cause_by)) : $this->root_cause_by;
        $this->resolution->root_cause_date = $this->root_cause_date ?: null;
        $this->resolution->action_taken = $this->action_taken;
        $this->resolution->action_taken_date = $this->action_taken_date ?: null;
        $this->resolution->action_taken_by = is_array($this->action_taken_by) ? implode(', ', array_filter($this->action_taken_by)) : $this->action_taken_by;
        $this->resolution->corrective_action_taken = $this->corrective_action_taken;
        $this->resolution->corrective_action_date = $this->corrective_action_date ?: null;
        $this->resolution->corrective_action_by = is_array($this->corrective_action_by) ? implode(', ', array_filter($this->corrective_action_by)) : $this->corrective_action_by;
        $this->resolution->car_required = (bool) $this->car_required;
        $this->resolution->ncr_required = (bool) $this->ncr_required;
        
        $this->resolution->save();
        
        // Notify child components to save their local state as well
        $this->dispatch('save-investigation-draft');
        $this->dispatch('sync-and-save-draft'); // Browser event for Alpine

        if ($notify) {
            $this->dispatch('alert', ['type' => 'success', 'message' => 'Progress saved as draft.']);
        }
        
        $this->dispatch('progress-saved');
    }

    public function persistDraft()
    {
        $this->performDraftSave(false);
        $this->dispatch('progress-saved-silently', time: now()->format('H:i:s'));
    }

    public function saveCauseOnly()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        
        $this->validate([
            'cause_of_complaint' => 'required|string',
            'root_cause_by' => 'required|array|min:1',
            'root_cause_date' => 'required|date',
        ], [
            'cause_of_complaint.required' => 'Please detail the nature and cause of the complaint.',
            'root_cause_by.required' => 'Please identify who performed the investigation.',
        ]);

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
        }

        $this->resolution->cause_of_complaint = $this->cause_of_complaint;
        $this->resolution->root_cause_by = is_array($this->root_cause_by) ? implode(', ', array_filter($this->root_cause_by)) : $this->root_cause_by;
        $this->resolution->root_cause_date = $this->root_cause_date ?: null;
        $this->resolution->save();
        
        $this->resolution->refresh();
        $this->cause_of_complaint = $this->resolution->cause_of_complaint;

        $this->isEditing = false;
        $this->showCheckpoint = true;
        $this->hasExistingInvestigation = true;
        
        // Ensure state is fully recalculated before dispatching
        $this->refreshState();
        
        $this->dispatch('initiate-workflow-action', action: 'investigationCheckpoint');
        $this->dispatch('progress-saved');
    }

    public function saveDecision()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
        }

        $this->resolution->car_required = (bool) $this->car_required;
        $this->resolution->ncr_required = (bool) $this->ncr_required;
        
        $this->resolution->save();

        // Log to Chain of Custody
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $decisionText = $this->car_required ? ($this->ncr_required ? "CAR & NC ASSIGNED" : "CAR ASSIGNED") : "NO CAR ASSIGNED";
        $chain->action = "Classification Determined: " . $decisionText . " & Submitted to Verification";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[2] ?? "Complaint Investigation";
        $chain->save();

        $this->checkpoint_completed = true;
        $this->showSuccess('Workflow classification recorded. You can now proceed with the resolution actions.');
        
        $this->dispatch('close-decision-modal');
        $this->dispatch('checkpoint-completed');
        $this->dispatch('complaint-workflow-updated'); 
        $this->dispatch('refresh-workflow');
        
        // Auto-switch based on decisions
        $this->carDecision = $this->car_required ? 'yes' : 'no';
        $this->ncDecision = $this->ncr_required ? 'yes' : 'no';
        
        $this->calculateCurrentStep();
    }

    public function saveInvestigation()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        $workflow_stage = $this->complaint->complaint_workflow;

        $rules = [
            'cause_of_complaint' => 'required|string',
        ];

        $messages = [
            'cause_of_complaint.required' => 'Please detail the nature and cause of the complaint.',
        ];

        // Actions are only mandatory in Stage 3 (Verification) for No-CAR paths
        // in Stage 2, only the Cause is required to move forward
        if (!$this->car_required && $workflow_stage >= 3) {
            $rules['action_taken'] = 'required|string';
            $rules['action_taken_date'] = 'required|date';
            $rules['action_taken_by'] = 'required|array|min:1';
            $rules['corrective_action_taken'] = 'required|string';
            $rules['corrective_action_date'] = 'required|date';
            $rules['corrective_action_by'] = 'required|array|min:1';

            $messages['action_taken.required'] = 'Please provide the immediate action taken.';
            $messages['action_taken_by.required'] = 'Please identify who performed the immediate action.';
            $messages['corrective_action_taken.required'] = 'Please provide the corrective action taken.';
            $messages['corrective_action_by.required'] = 'Please identify who performed the corrective action.';
        }

        $this->validate($rules, $messages);

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
        }

        $this->resolution->cause_of_complaint = $this->cause_of_complaint;
        
        if ($this->car_required) {
            $this->resolution->action_taken = null;
            $this->resolution->action_taken_date = null;
            $this->resolution->action_taken_by = null;
            $this->resolution->corrective_action_taken = null;
            $this->resolution->corrective_action_date = null;
            $this->resolution->corrective_action_by = null;
        } else {
            $this->resolution->action_taken = $this->action_taken;
            $this->resolution->action_taken_date = $this->action_taken_date;
            $this->resolution->action_taken_by = is_array($this->action_taken_by) ? implode(', ', array_filter($this->action_taken_by)) : $this->action_taken_by;
            $this->resolution->corrective_action_taken = $this->corrective_action_taken;
            $this->resolution->corrective_action_date = $this->corrective_action_date;
            $this->resolution->corrective_action_by = is_array($this->corrective_action_by) ? implode(', ', array_filter($this->corrective_action_by)) : $this->corrective_action_by;
        }
        
        $this->resolution->car_required = (bool) $this->car_required;
        $this->resolution->ncr_required = (bool) $this->ncr_required;

        // Routing Logic based on CAR
        if ($this->car_required) {
            // YES CAR Required -> Generate CAR Number
            if (!$this->resolution->car_no) {
                $resolutions_count = Complaintsresolutions::where('complaint_id', $this->complaint->id)->count() + 1;
                $this->resolution->car_no = $this->complaint->complaint_id . "CAR" . $resolutions_count;
            }
            $actionText = 'Investigation findings logged by ' . (Auth::user()?->name ?? 'System') . '. Corrective Action Request (CAR) initiated.';
        } else {
            // NO CAR Required
            $actionText = 'Investigation details and containment actions logged by ' . (Auth::user()?->name ?? 'System') . '.';
            $this->resolution->car_no = null;
        }
        
        // Stay in Stage 2 (Investigation) - Advancement now requires manual approval in Workflow tab
        $this->resolution->workflow_stage = 2; 
        $this->resolution->save();

        app(ComplaintInvestigationReportService::class)
            ->generateAndAttachInvestigationReport($this->complaint);
        
        $this->resolution->refresh();
        $this->cause_of_complaint = $this->resolution->cause_of_complaint;
        $this->action_taken = $this->resolution->action_taken;
        $this->corrective_action_taken = $this->resolution->corrective_action_taken;

        // Log progress to Chain of Custody but do NOT change complaint stage yet
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = $actionText;
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[2] ?? "Complaint Investigation";
        $chain->save();

        $this->isEditing = false;
        
        // Refresh checkpoint status to ensure UI buttons (Change Decision, etc.) appear immediately
        $this->checkpoint_completed = \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
            ->where('action', 'like', 'Classification Determined%')
            ->exists();

        $this->dispatch('close-investigation-modal');
        $this->dispatch('attachment-added');
        $this->dispatch('complaint-workflow-updated');
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        
        // Notify child components to toggle their edit state (if any)
        $this->dispatch('toggle-active-step-edit', isEditing: $this->isEditing);

        if ($this->isEditing) {
            if ($this->resolution) {
                $this->resolution->refresh();
            }
            $this->determineActiveStep();
        }
    }

    public function discardChanges()
    {
        if ($this->resolution) {
            $this->resolution->refresh();
            $this->cause_of_complaint = $this->resolution->cause_of_complaint;
            $this->action_taken = $this->resolution->action_taken;
            $this->action_taken_date = $this->resolution->action_taken_date?->format('Y-m-d');
            $this->corrective_action_taken = $this->resolution->corrective_action_taken;
            $this->corrective_action_date = $this->resolution->corrective_action_date?->format('Y-m-d');
        }
        $this->isEditing = false;
        $this->dispatch('toggle-active-step-edit', isEditing: false);
    }

    public function openInvestigationModal()
    {
        $this->activeModalTab = 'cause';
        $this->dispatch('show-investigation-modal');
    }

    public function setActiveModalTab($tab)
    {
        $this->activeModalTab = $tab;
    }

    public function generateReport()
    {
        $complaint = Complaint::with(['client'])->find($this->complaint->id);
        
        // Use the current component properties for the resolution data in the report
        // This allows generating a report "so far" even if not fully saved in the DB yet
        $resolution = $this->resolution ?? new Complaintsresolutions();
        $resolution->cause_of_complaint = $this->cause_of_complaint;
        $resolution->action_taken = $this->action_taken;
        $resolution->action_taken_date = $this->action_taken_date;
        $resolution->corrective_action_taken = $this->corrective_action_taken;
        $resolution->corrective_action_date = $this->corrective_action_date;
        $resolution->car_required = $this->car_required;

        $chainOfCustody = Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)->with('actionTaker')->get();

        $pdf = Pdf::loadView('pdfs.investigation_report', compact('complaint', 'resolution', 'chainOfCustody'));
        
        $safeId = str_replace(['/', '\\'], '-', $complaint->complaint_id);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'Laboratory_Investigation_Report_' . $safeId . '.pdf');
    }

    /**
     * Save both remarks from the Review & Close section and close the complaint.
     * This mirrors the resolution-approval final save which may also be triggered
     * from the Workflow tab; providing it here keeps the Investigation stepper
     * able to finalize the complaint directly.
     */
    public function saveAndCloseComplaint()
    {
        $this->checkPermission('CRM.components.Resolution Approval.Edit');

        $this->validate([
            'client_remarks' => 'required|string',
            'complaint_review_remarks' => 'required|string',
        ], [
            'client_remarks.required' => 'Client remarks are required before closing the complaint.',
            'complaint_review_remarks.required' => 'Complaint review remarks are required before closing the complaint.',
        ]);

        try {
            DB::transaction(function (): void {
                $resolution = Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
                if (!$resolution) {
                    $resolution = new Complaintsresolutions();
                    $resolution->complaint_id = $this->complaint->id;
                }

                $resolution->client_remarks = $this->client_remarks;
                $resolution->internal_remarks = $this->complaint_review_remarks;
                $resolution->save();

                $this->complaint->complaint_workflow = 5;
                $this->complaint->is_closed = true;
                $this->complaint->date_closed = now();
                $this->complaint->closed_by = Auth::id();
                $this->complaint->save();

                $chain = new Chain_of_Custody_Complaint();
                $chain->complaint_id = $this->complaint->id;
                $chain->action = 'Complaint Closed by ' . (Auth::user()?->name ?? 'System') . '. Final remarks recorded.';
                $chain->action_taker_id = Auth::id();
                $chain->workflow_stage = getComplaintWorkflow()[4] ?? 'Resolution Approval';
                $chain->comments = $this->complaint_review_remarks;
                $chain->move_out_date = now();
                $chain->save();

                $this->complaint->refresh();

                // Dispatch report generation to background
                GenerateCloseReports::dispatch($this->complaint->id);
            });
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->dispatch('attachment-added');
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed successfully with remarks.']);
        $this->dispatch('complaint-workflow-updated');
    }

    public function getStepButtonProperty()
    {
        // Button only shows when not editing
        if ($this->isEditing) {
            return ['show' => false, 'label' => '', 'click' => ''];
        }

        // Step 1: Cause
        if ($this->currentStep == 1) {
            if (empty($this->cause_of_complaint)) {
                return ['show' => true, 'label' => 'Add Cause of Complaint', 'click' => '$wire.toggleEdit()'];
            }
            if (!$this->checkpoint_completed) {
                return ['show' => true, 'label' => 'Determine CAR Decision', 'click' => '$dispatch(\'show-decision-modal\')'];
            }
            // If checkpoint completed, no button for step 1
        }

        // Step 2: Immediate Actions
        elseif ($this->carDecision === 'yes') {
            if ($this->ncDecision === 'yes') {
                // CAR + NC: Step 2 is NC
                if (!$this->isNcCompleted()) {
                    return ['show' => true, 'label' => 'Add Non-Conformance', 'click' => '$wire.toggleEdit()'];
                } elseif (!$this->isCapaCompleted()) {
                    return ['show' => true, 'label' => 'Add CAPA', 'click' => '$wire.toggleEdit()'];
                }
            } else {
                // CAR only: Step 2 is CAPA
                if (!$this->isCapaCompleted()) {
                    return ['show' => true, 'label' => 'Add CAPA', 'click' => '$wire.toggleEdit()'];
                }
            }
        } elseif ($this->currentStep == 2) {
            // No CAR: Step 2 is Corrective Actions
            if (empty($this->action_taken) || empty($this->corrective_action_taken)) {
                return ['show' => true, 'label' => 'Add Corrective Actions', 'click' => '$wire.toggleEdit()'];
            }
        }

        // Step 3/4: Review - show Add CAPA if CAR required and not completed
        elseif ($this->carDecision === 'yes' && !$this->isCapaCompleted()) {
            return ['show' => true, 'label' => 'Add CAPA', 'click' => '$wire.toggleEdit()'];
        }

        return ['show' => false, 'label' => '', 'click' => ''];
    }

    public function handleInvestigationAction()
    {
        if (!$this->hasExistingInvestigation) {
            // Add Investigation Findings - go to step 1 and enable editing
            $this->goToStep(1, true);
            return;
        }

        if (!$this->checkpoint_completed) {
            // Determine CAR Decision - show decision modal
            $this->dispatch('show-decision-modal');
            return;
        }

        // Dynamic actions based on current step
        if ($this->currentStep == 1) {
            // Edit Investigation Findings
            $this->goToStep(1, true);
            return;
        }

        if ($this->currentStep == 2 && $this->carDecision === 'no') {
            // Enable editing for corrective actions
            $this->isEditing = true;
            return;
        }

        if ($this->carDecision === 'yes') {
            if ($this->ncDecision === 'yes' && $this->currentStep == 2) {
                // Edit Non-Conformance - NC tab is shown in step 2, enable editing
                $this->goToStep(2, true);
                return;
            }

            if (($this->ncDecision === 'yes' && $this->currentStep == 3) || ($this->ncDecision === 'no' && $this->currentStep == 2)) {
                // Edit CAPA Plan - CAPA tab is shown in step 2 (CAR only) or step 3 (CAR+NC), enable editing
                $targetStep = ($this->ncDecision === 'yes') ? 3 : 2;
                $this->goToStep($targetStep, true);
                return;
            }
        }

        // Default: Edit Investigation Details - go to step 1
        $this->goToStep(1, true);
    }

    public function getInvestigationActionProperty()
    {
    if (!$this->hasExistingInvestigation) {
        return [
            'label' => 'Add Cause of Complaint',
            'icon' => 'mdi-plus'
        ];
    }

        if (!$this->checkpoint_completed) {
            return [
                'label' => 'Determine CAR Decision',
                'icon' => 'mdi-alert-decagram-outline'
            ];
        }

    // --- Dynamic labels based on the active step ---
    if ($this->currentStep == 1) {
        if (empty($this->cause_of_complaint)) {
            return [
                'label' => 'Add Cause of Complaint',
                'icon' => 'mdi-plus'
            ];
        } else {
            return [
                'label' => 'Edit Cause of Complaint',
                'icon' => 'mdi-pencil-outline'
            ];
        }
    }

    if ($this->currentStep == 2 && $this->carDecision === 'no') {
        return [
            'label' => 'Add Actions Taken',
            'icon' => 'mdi-plus'
        ];
    }

    if ($this->carDecision === 'yes') {
        if ($this->ncDecision === 'yes' && $this->currentStep == 2) {
            if (!$this->isNcCompleted()) {
                return [
                    'label' => 'Add Non-Conformance',
                    'icon' => 'mdi-plus'
                ];
            } else {
                return [
                    'label' => 'Edit Non-Conformance',
                    'icon' => 'mdi-pencil-outline'
                ];
            }
        }

        if (($this->ncDecision === 'yes' && $this->currentStep == 3) || ($this->ncDecision === 'no' && $this->currentStep == 2)) {
            if (!$this->isCapaCompleted()) {
                return [
                    'label' => 'Add CAPA',
                    'icon' => 'mdi-plus'
                ];
            } else {
                return [
                    'label' => 'Edit CAPA',
                    'icon' => 'mdi-pencil-outline'
                ];
            }
        }
    }

        return [
            'label' => 'Edit Investigation Details',
            'icon' => 'mdi-pencil-outline'
        ];
    }

    /**
     * Get the full lifecycle history for the review timeline.
     */
    public function getTimelineProperty()
    {
        return Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
            ->with('actionTaker')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-investigation-tab');
    }

}

