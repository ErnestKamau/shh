<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\CapaRecord;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;

class ComplaintCapaTab extends BaseCrmComponent
{
    // CAPA Internal Workflow Statuses
    public const STATUS_DRAFT = 'Draft';
    public const STATUS_ASSIGNED = 'Assigned';
    public const STATUS_TRIAGED = 'Triaged';
    public const STATUS_ACTION = 'Action';
    public const STATUS_VERIFY = 'Verify';
    public const STATUS_COMPLETED = 'Completed';

    public $complaintId;
    public $complaint;
    public $capaRecord;
    public $resolution;

    // View State
    public $isCapaSaved = false;
    public bool $isEditing = false; 
    public int $activeStep = 1;
    public string $capa_status = self::STATUS_DRAFT;

    // CAPA Fields (Action Plan)
    public $issued_to = [];
    public $issued_by = [];
    public $date_issued = '';
    public $proposed_close_out_date = '';
    public $ref_clause = '';
    public $car_type = ''; // Major/Minor
    public $capa_risk_level = ''; // Low/Medium/High
    public $action_taken = ''; 
    public $acceptance = '';
    public $capa_identified_by = [];
    public $capa_identified_date = '';
    public $capa_corrective_action = '';
    public $root_cause = '';
    public $root_cause_by = [];
    public $root_cause_date = '';
    public $action_taken_by = [];
    public $action_taken_date = '';
    public $corrective_action_by = [];
    public $corrective_action_date = '';
    public $corrective_action_by_user = null;
    public $lab_no = '';
    public $details_of_non_conformance = '';

    // Verification
    public $effectiveness_verified_by = [];
    public $effectiveness_date = '';

    public function getCapaButtonLabelProperty()
    {
        if ($this->capa_status === self::STATUS_COMPLETED) {
            return 'Review/Edit CAPA';
        }

        return match($this->capa_status) {
            self::STATUS_DRAFT    => 'Fill Case Assignment',
            self::STATUS_ASSIGNED => 'Continue to Non-Conformance Details',
            self::STATUS_TRIAGED  => 'Continue to Corrective Action Plan',
            self::STATUS_ACTION,
            self::STATUS_VERIFY   => 'Continue to Acceptance & Effectiveness of Actions',
            default               => 'Add CAPA'
        };
    }

    #[On('toggle-active-step-edit')]
    public function handleToggleEdit($isEditing)
    {
        $this->isEditing = $isEditing;
        if (!$this->isEditing) {
            $this->mount($this->complaint->id);
        }
    }

    #[On('trigger-next-action')]
    public function handleTriggerNextAction($action)
    {
        if ($action === 'add_capa') {
            $this->isEditing = true;
            $this->activeStep = 1;
        } elseif ($action === 'add_plan') {
            $this->isEditing = true;
            $this->activeStep = 3;
        } elseif ($action === 'add_verification') {
            $this->isEditing = true;
            $this->activeStep = 4;
        } elseif ($action === 'toggle_edit') {
            $this->toggleEdit();
        }
    }

    #[On('nc-completed')]
    public function handleNcCompleted()
    {
        $this->isEditing = true;
        Log::info("CAPA Tab: NC Completed event received. Re-deriving status.");
        $this->mount($this->complaint->id, true);
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if (!$this->isEditing) {
            $this->mount($this->complaint->id); // Reset data if cancelling
        }
    }

    public function mount($complaintId, $isEditing = false, $prefilledData = [])
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->isEditing = $isEditing;
        $this->complaint = Complaint::findOrFail($complaintId);
        
        $this->capaRecord = CapaRecord::where('complaint_id', $this->complaintId)->first();
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();

        // Fallback: If CAR is required but car_no is missing, generate it now
        if ($this->resolution && $this->resolution->car_required && empty($this->resolution->car_no)) {
            $resolutions_count = Complaintsresolutions::where('complaint_id', $this->complaintId)->count();
            $this->resolution->car_no = $this->complaint->complaint_id . "CAR" . max(1, $resolutions_count);
            $this->resolution->save();
        }

        // 1. Initialize CAPA Data (Action Plan)
        if ($this->resolution) {
            $this->acceptance = $this->resolution->findings;
            $this->action_taken = $this->resolution->action_taken;
            $this->action_taken_by = $this->resolution->action_taken_by ? array_map('trim', explode(',', $this->resolution->action_taken_by)) : [];
            $this->action_taken_date = $this->resolution->action_taken_date ? $this->resolution->action_taken_date->format('Y-m-d') : now()->format('Y-m-d');
            
            $this->root_cause = $this->resolution->root_cause_analysis;
            $this->root_cause_by = $this->resolution->root_cause_by ? array_map('trim', explode(',', $this->resolution->root_cause_by)) : [];
            $this->root_cause_date = $this->resolution->root_cause_date ? $this->resolution->root_cause_date->format('Y-m-d') : now()->format('Y-m-d');
            
            $this->issued_to = $this->resolution->issued_to ? array_map('trim', explode(',', $this->resolution->issued_to)) : [];

            $this->capa_corrective_action = $this->resolution->corrective_action_taken;
            $this->corrective_action_by = $this->resolution->corrective_action_by ? array_map('trim', explode(',', $this->resolution->corrective_action_by)) : [];
            $this->corrective_action_date = $this->resolution->corrective_action_date ? $this->resolution->corrective_action_date->format('Y-m-d') : now()->format('Y-m-d');

            $this->issued_to = $this->resolution->issued_to ? array_map('trim', explode(',', $this->resolution->issued_to)) : [];
            $this->issued_by = $this->resolution->issued_by ? array_map('trim', explode(',', $this->resolution->issued_by)) : [];
            $this->date_issued = $this->resolution->date_issued ? $this->resolution->date_issued->format('Y-m-d') : now()->format('Y-m-d');
            
            $dbProposedDate = $this->resolution->proposed_close_out_date ? $this->resolution->proposed_close_out_date->format('Y-m-d') : null;
            
            // Force 25-day gap if no date set OR if it's currently the same as issue date (likely an old bug fallback)
            if (!$dbProposedDate || $dbProposedDate === $this->date_issued) {
                $this->proposed_close_out_date = \Carbon\Carbon::parse($this->date_issued)->addDays(25)->format('Y-m-d');
            } else {
                $this->proposed_close_out_date = $dbProposedDate;
            }
            $this->ref_clause = $this->resolution->ref_clause;
            $this->car_type = $this->resolution->car_type ?: '';
            $this->capa_risk_level = $this->resolution->risk_level ?: '';
            $this->capa_identified_by = $this->resolution->capa_identified_by ? array_map('trim', explode(',', $this->resolution->capa_identified_by)) : [];
            $this->capa_identified_date = $this->resolution->capa_identified_date ? $this->resolution->capa_identified_date->format('Y-m-d') : now()->format('Y-m-d');

            if (!empty($this->corrective_action_by)) {
                $firstName = $this->corrective_action_by[0] ?? null;
                if ($firstName) {
                    $this->corrective_action_by_user = \App\User::where('name', $firstName)->first();
                }
            }
        } else {
            // Default Dates
            $this->action_taken_date = now()->format('Y-m-d');
            $this->root_cause_date = now()->format('Y-m-d');
            $this->corrective_action_date = now()->format('Y-m-d');
            $this->date_issued = now()->format('Y-m-d');
            $this->capa_identified_date = now()->format('Y-m-d');
            $this->proposed_close_out_date = now()->addDays(25)->format('Y-m-d');
        }
            
            // Fallback for existing records with no close out date or where it's mistakenly equal to issue date
            if ((empty($this->proposed_close_out_date) || $this->proposed_close_out_date === $this->date_issued) && !empty($this->date_issued)) {
                $this->proposed_close_out_date = \Carbon\Carbon::parse($this->date_issued)->addDays(25)->format('Y-m-d');
            }
            
            // Initial data loading
            if ($this->capaRecord) {
                $this->lab_no = $this->capaRecord->lab_no;
                $this->details_of_non_conformance = $this->capaRecord->details_of_non_conformance;
                
                $this->effectiveness_verified_by = $this->capaRecord->effectiveness_verified_by ? array_map('trim', explode(',', $this->capaRecord->effectiveness_verified_by)) : [];
                $this->effectiveness_date = $this->capaRecord->effectiveness_date ? \Carbon\Carbon::parse($this->capaRecord->effectiveness_date)->format('Y-m-d') : now()->format('Y-m-d');
            } else {
                $this->effectiveness_date = now()->format('Y-m-d');
            }

            if (empty($this->details_of_non_conformance)) {
                $this->details_of_non_conformance = $this->complaint->description;
            }

            // Autopick the lab report no. strictly if lab-related
            if ($this->complaint->is_lab_related) {
                if (empty($this->lab_no)) {
                    $this->lab_no = $this->complaint->report_serial_no;
                }
            }
           
            // If essential CAPA is already filled out, mark as saved
            if (!empty($this->acceptance) && !empty($this->root_cause) && !empty($this->capa_corrective_action)) {
                $this->isCapaSaved = true;
            }



        // 2. Initialize Verification Data
        if ($this->capaRecord) {
            $this->effectiveness_verified_by = $this->capaRecord->effectiveness_verified_by ? array_map('trim', explode(',', $this->capaRecord->effectiveness_verified_by)) : [];
            $this->effectiveness_date = $this->capaRecord->effectiveness_date ? \Carbon\Carbon::parse($this->capaRecord->effectiveness_date)->format('Y-m-d') : '';

            $whys = $this->capaRecord->why_why_analysis ?? [];
            if (is_string($whys)) {
                $whys = json_decode($whys, true) ?? [];
            }
            // Corrective action and root cause are now primarily managed in the resolution table columns
            // to ensure synchronization across sections. Pulling from legacy JSON here was overwriting valid data.
        }

        // 3. Set default view based on progression

        // 4. Derive Internal CAPA Status
        $this->deriveCapaStatus();
    }

    public function getCapaStatusIndexProperty(): int
    {
        $map = [
            self::STATUS_DRAFT => 0,
            self::STATUS_TRIAGED => 1,
            self::STATUS_ACTION => 2,
            self::STATUS_VERIFY => 3,
            self::STATUS_COMPLETED => 4,
        ];
        return $map[$this->capa_status] ?? 0;
    }

    public function updatedActiveStep($value)
    {
        // Autopopulate verification date when step 4 is activated
        if ($value === 4 && empty($this->effectiveness_date)) {
            $this->effectiveness_date = now()->format('Y-m-d');
        }
    }

    public function updatedDateIssued($value)
    {
        if (!empty($value)) {
            $this->proposed_close_out_date = \Carbon\Carbon::parse($value)->addDays(25)->format('Y-m-d');
        }
    }

    public function getIsReadyToAdvanceProperty()
    {
        // Validation for NC/CAPA completeness
        $hasNC = !empty(trim(strip_tags((string)$this->details_of_non_conformance)));
        $hasAction = !empty(trim(strip_tags((string)$this->capa_corrective_action)));
        $hasImmediateAction = !empty(trim(strip_tags((string)$this->action_taken)));
        $hasRootCause = !empty(trim(strip_tags((string)$this->root_cause)));
        
        $hasAssignment = !empty($this->issued_to) && 
                         !empty($this->issued_by) && 
                         !empty($this->date_issued) && 
                         !empty($this->proposed_close_out_date);

        $hasNCMeta = !empty($this->capa_identified_by) && !empty($this->capa_identified_date);
        
        return $hasNC && $hasAction && $hasRootCause && $hasImmediateAction && $hasAssignment && $hasNCMeta;
    }

    public function getNextPendingActionProperty()
    {
        $status = $this->capa_status;
        $cleanDetails = trim(strip_tags((string)$this->details_of_non_conformance));
        $cleanAction = trim(strip_tags((string)$this->action_taken));
        $cleanRootCause = trim(strip_tags((string)$this->root_cause));
        $cleanCorrectiveAction = trim(strip_tags((string)$this->capa_corrective_action));
        $cleanAcceptance = trim(strip_tags((string)$this->acceptance));

        if (empty($this->issued_to) || empty($this->issued_by)) return "Assign Personnel & Dates";
        if (empty($cleanDetails) || empty($this->car_type)) return "Describe Non-Conformance";
        if (empty($cleanRootCause) || empty($cleanCorrectiveAction)) return "Formulate RCA & Action Plan";
        if (empty($this->effectiveness_verified_by) || empty($cleanAcceptance)) return "Verify Effectiveness & Approve";
        
        return "CAPA Completed";
    }

    protected function deriveCapaStatus()
    {
        // Use stripped/trimmed strings to detect real content
        $cleanDetails = trim(strip_tags((string)$this->details_of_non_conformance));
        $cleanAction = trim(strip_tags((string)$this->action_taken));
        $cleanRootCause = trim(strip_tags((string)$this->root_cause));
        $cleanCorrectiveAction = trim(strip_tags((string)$this->capa_corrective_action));
        $cleanAcceptance = trim(strip_tags((string)$this->acceptance));

        // 1. Check Step 1 Completion (Assignment)
        if (empty($this->issued_to) || empty($this->issued_by) || empty($this->date_issued) || empty($this->proposed_close_out_date)) {
            $this->capa_status = self::STATUS_DRAFT;
            $this->activeStep = 1;
            return;
        }

        // 2. Check Step 2 Completion (Non-Conformance)
        if (empty($cleanDetails) || empty($this->car_type) || empty($this->capa_identified_by) || empty($this->capa_identified_date)) {
            $this->capa_status = self::STATUS_ASSIGNED;
            $this->activeStep = 2;
            return;
        }

        // 3. Check Step 3 Completion (Corrective Action Plan)
        if (empty($this->capa_risk_level) || empty($cleanRootCause) || empty($cleanCorrectiveAction) || empty($cleanAction) || empty($this->root_cause_by) || empty($this->corrective_action_by)) {
            $this->capa_status = self::STATUS_TRIAGED;
            $this->activeStep = 3;
            return;
        }

        // 4. Check Step 4 Completion (Verification)
        if (!empty($this->effectiveness_verified_by) && !empty($this->effectiveness_date) && !empty($cleanAcceptance)) {
            $this->capa_status = self::STATUS_COMPLETED;
            $this->activeStep = 4;
        } else if ($this->effectiveness_verified_by || $this->effectiveness_date) {
            $this->capa_status = self::STATUS_VERIFY;
            $this->activeStep = 4;
        } else {
            $this->capa_status = self::STATUS_ACTION;
            $this->activeStep = 4;
        }

        // Autopopulate verification date when entering final step
        if ($this->activeStep == 4 && empty($this->effectiveness_date)) {
            $this->effectiveness_date = now()->format('Y-m-d');
        }
    }

    public function getIsRcaCompletedProperty(): bool
    {
        // Now RCA is in NC tab, we check if it's present in capaRecord
        if (!$this->capaRecord) return false;
        $whys = $this->capaRecord->why_why_analysis ?? [];
        if (is_string($whys)) $whys = json_decode($whys, true) ?? [];
        return !empty(strip_tags($whys['problem_statement'] ?? '')) && !empty(strip_tags($whys['why_1'] ?? ''));
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->mount($this->complaintId); // Re-load from DB to discard unsaved changes
        $this->dispatch('edit-mode-deactivated');
        $this->dispatch('section-toggled'); // Trigger re-init of any needed JS
    }

    /**
     * Bridge Methods for Rich Text synchronization from the frontend
     */
    public function applyRichTextData(array $data)
    {
        foreach ($data as $field => $content) {
            if (property_exists($this, $field)) {
                $this->$field = $content;
            }
        }
    }

    public function syncRichTextAndAutosave(array $data)
    {
        $this->applyRichTextData($data);
        $this->saveAllData();
        $this->dispatch('autosave-completed', ['time' => now()->format('H:i:s')]);
    }

    public function syncRichTextAndComplete(array $data)
    {
        $this->applyRichTextData($data);
        $this->saveFinalVerification([]);
    }

    /**
     * Section-Specific Save Methods
     */
    public function saveAssignment(array $richTextData = [])
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        if (!empty($richTextData)) $this->applyRichTextData($richTextData);
        $this->saveAllData(); // Save as draft first

        $this->validate([
            'date_issued' => 'required|date',
            'proposed_close_out_date' => 'required|date',
            'issued_to' => 'required|array|min:1',
            'lab_no' => ($this->complaint->is_lab_related ? 'required' : 'nullable') . '|string',
            'issued_by' => 'required|array|min:1',
        ], [
            'issued_to.required' => 'Please identify who this CAPA is issued to.',
            'issued_by.required' => 'Please identify who issued this CAPA.',
        ]);

        $this->deriveCapaStatus();
        $isFullEdit = ($this->capa_status === self::STATUS_COMPLETED);
        if (!$isFullEdit) {
            $this->activeStep = 2;
            $this->dispatch('capa-step-saved', ['nextStep' => 2]);
        }
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Assignment details saved.']);
    }

    public function saveNcDetails(array $richTextData = [])
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        if (!empty($richTextData)) $this->applyRichTextData($richTextData);
        
        $this->saveAllData(); // Save as draft even if validation fails

        $this->validate([
            'details_of_non_conformance' => 'required|string',
            'car_type' => 'required',
            'capa_identified_by' => 'required|array|min:1',
            'capa_identified_date' => 'required|date',
        ], [
            'capa_identified_by.required' => 'Please identify who found the non-conformance.',
            'capa_identified_date.required' => 'The identification date is required.',
        ]);

        $this->deriveCapaStatus();
        $isFullEdit = ($this->capa_status === self::STATUS_COMPLETED);
        if (!$isFullEdit) {
            $this->activeStep = 3;
            $this->dispatch('capa-step-saved', ['nextStep' => 3]);
        }
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Non-conformance details saved.']);
    }


    public function saveCorrectiveAction(array $richTextData = [])
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        if (!empty($richTextData)) $this->applyRichTextData($richTextData);
        
        $this->saveAllData(); // Save draft before validation

        $this->validate([
            'capa_risk_level' => 'required',
            'action_taken' => 'required',
            'action_taken_by' => 'required|array|min:1',
            'action_taken_date' => 'required|date',
            'root_cause' => 'required',
            'root_cause_by' => 'required|array|min:1',
            'root_cause_date' => 'required|date',
            'capa_corrective_action' => 'required',
            'corrective_action_by' => 'required|array|min:1',
            'corrective_action_date' => 'required|date',
        ], [
            'action_taken_by.required' => 'Identify who performed the immediate action.',
            'root_cause_by.required' => 'Identify who determined the root cause.',
            'corrective_action_by.required' => 'Identify who proposed the corrective action.',
        ]);

        $this->deriveCapaStatus();
        $isFullEdit = ($this->capa_status === self::STATUS_COMPLETED);
        if (!$isFullEdit) {
            $this->activeStep = 4;
            $this->dispatch('capa-step-saved', ['nextStep' => 4]);
        }
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Corrective action plan saved.']);
    }

    public function saveFinalVerification(array $richTextData = [])
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        if (!empty($richTextData)) $this->applyRichTextData($richTextData);
        
        $this->validate([
            // Triage
            'issued_to' => 'required|array',
            'issued_by' => 'required|array',
            'date_issued' => 'required|date',
            'proposed_close_out_date' => 'required|date|after_or_equal:date_issued',
            'car_type' => 'required',
            'capa_risk_level' => 'required',
            
            // Action Plan
            'root_cause' => 'required',
            'capa_corrective_action' => 'required',
            'action_taken' => 'required',

            // Verification
            'effectiveness_verified_by' => 'required|array',
            'effectiveness_verified_by.*' => 'string',
            'effectiveness_date' => 'required|date',
            'acceptance' => 'required',
        ], [
            'proposed_close_out_date.after_or_equal' => 'The target closure date must be on or after the issue date.',
        ]);

        $this->saveAllData();
        $this->deriveCapaStatus();
        
        $this->isEditing = false;
        $this->dispatch('alert', ['type' => 'success', 'message' => 'CAPA has been successfully verified and finalized.']);
        $this->dispatch('capa-completed');
        $this->dispatch('initiate-workflow-action', action: 'approveCapa');
        $this->dispatch('refresh-workflow');
    }

    public function saveDraft()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        $this->saveAllData();
        $this->deriveCapaStatus();
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Progress saved.']);
    }

    #[On('save-investigation-draft')]
    public function persistDraft()
    {
        if (!$this->isEditing) return;
        $this->saveAllData();
        $this->deriveCapaStatus();
    }

    /**
     * Centralized Persistence Method
     */
    public function saveAllData()
    {
        if (!Auth::check()) return;

        // 1. Update Resolution
        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
            $this->resolution->workflow_stage = $this->complaint->complaint_workflow;
        }

        $this->resolution->issued_to = is_array($this->issued_to) ? implode(', ', array_filter($this->issued_to)) : $this->issued_to;
        $this->resolution->issued_by = is_array($this->issued_by) ? implode(', ', array_filter($this->issued_by)) : $this->issued_by;
        $this->resolution->date_issued = $this->date_issued ?: null;
        $this->resolution->proposed_close_out_date = $this->proposed_close_out_date ?: null;
        $this->resolution->ref_clause = $this->ref_clause;
        $this->resolution->car_type = $this->car_type;
        $this->resolution->risk_level = $this->capa_risk_level;
        $this->resolution->capa_identified_by = is_array($this->capa_identified_by) ? implode(', ', array_filter($this->capa_identified_by)) : $this->capa_identified_by;
        $this->resolution->capa_identified_date = $this->capa_identified_date ?: null;
        
        $this->resolution->findings = $this->acceptance;
        
        $this->resolution->root_cause_analysis = $this->root_cause;
        $this->resolution->root_cause_by = is_array($this->root_cause_by) ? implode(', ', array_filter($this->root_cause_by)) : $this->root_cause_by;
        $this->resolution->root_cause_date = $this->root_cause_date ?: null;
        
        $this->resolution->corrective_action_taken = $this->capa_corrective_action;
        $this->resolution->corrective_action_by = is_array($this->corrective_action_by) ? implode(', ', array_filter($this->corrective_action_by)) : $this->corrective_action_by;
        $this->resolution->corrective_action_date = $this->corrective_action_date ?: null;
        
        $this->resolution->action_taken = $this->action_taken;
        $this->resolution->action_taken_by = is_array($this->action_taken_by) ? implode(', ', array_filter($this->action_taken_by)) : $this->action_taken_by;
        $this->resolution->action_taken_date = $this->action_taken_date ?: null;
        
        $this->resolution->save();

        // 2. Update CAPA Record (Verification specific)
        if (!$this->capaRecord) {
            $this->capaRecord = new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }

        $this->capaRecord->effectiveness_verified_by = is_array($this->effectiveness_verified_by) ? implode(', ', array_filter($this->effectiveness_verified_by)) : $this->effectiveness_verified_by;
        $this->capaRecord->lab_no = $this->lab_no;
        $this->capaRecord->details_of_non_conformance = $this->details_of_non_conformance;
        $this->capaRecord->effectiveness_date = $this->effectiveness_date ?: null;
        
        // Corrective action is now managed directly in the resolution table
        $this->capaRecord->save();
    }

    public function rejectCapa()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $this->complaint->complaint_workflow = 2; // Back to Stage 2
        $this->complaint->save();
        
        if ($this->resolution) {
            $this->resolution->workflow_stage = 2;
            $this->resolution->save();
        }

        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = "CAPA Rejected. Returned to Investigation.";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[2] ?? "Complaint Investigation";
        $chain->save();

        $this->showSuccess('CAPA rejected. Complaint returned to Complaint Investigation.');
        $this->dispatch('complaint-workflow-updated');
    }

    public function downloadCapaReport()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        if (!$this->resolution) return;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.capa_report', [
            'complaint' => $this->complaint,
            'resolution' => $this->resolution,
            'capaRecord' => $this->capaRecord
        ]);

        $safeCarNo = str_replace(['/', '\\'], '-', $this->resolution->car_no);
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'CAPA_Report_' . $safeCarNo . '.pdf');
    }



    public function toggleRisk($level)
    {
        $this->capa_risk_level = ($this->capa_risk_level === $level) ? '' : $level;
    }



    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-capa-tab', [
            'users' => getAllUsers()
        ]);
    }
}
