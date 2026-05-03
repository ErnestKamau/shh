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

class ComplaintCapaTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $capaRecord;
    public $resolution;

    // View State
    public $activeView = 'capa'; // 'capa' or 'ncr'
    public $isCapaSaved = false;
    public bool $ncr_required = false; // Decision Gate 2: NCR/Why-Why toggle
    public string $step_status = 'ncr_pending_init'; // Tracks phase: ncr_pending_init, ncr_in_progress, ncr_verification, capa_completed
    public bool $isNcrSaved = false;
    public bool $isEditing = false; // Dual-State master switch

    // CAPA Fields (Action Plan - Image 2)
    public $issued_to = '';
    public $issued_by = '';
    public $date_issued = '';
    public $proposed_close_out_date = '';
    public $ref_clause = '';
    public $car_type = ''; // Major/Minor
    public $risk_level = ''; // Low/Medium/High
    public $action_taken = ''; 
    public $acceptance = '';
    public $capa_identified_by = '';
    public $capa_identified_date = '';

    // NCR Fields (Non-Conformance & RCA - Image 4)
    public $lab_no = '';
    public $details_of_non_conformance = '';
    public $identified_by = '';
    public $ncr_identified_date = '';
    public $why_1 = '';
    public $why_2 = '';
    public $why_3 = '';
    public $why_4 = '';
    public $why_5 = '';
    public $root_cause = '';
    public $capa_corrective_action = '';
    public $problem_statement = '';

    // Verification
    public $effectiveness_verified_by = '';
    public $effectiveness_date = '';

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::findOrFail($complaintId);
        
        $this->capaRecord = CapaRecord::where('complaint_id', $this->complaintId)->first();
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();

        // Fallback: If CAR is required but car_no is missing, generate it now
        if ($this->resolution && $this->resolution->car_required && empty($this->resolution->car_no)) {
            $resolutions_count = Complaintsresolutions::where('complaint_id', $this->complaintId)->count();
            $this->resolution->car_no = $this->complaint->complaint_id . "CAR" . max(1, $resolutions_count);
            $this->resolution->save();
        }

        // Auto-populate lab_no if empty
        if (!$this->capaRecord || empty($this->capaRecord->lab_no)) {
            $this->lab_no = $this->complaint->report_serial_no ?: ($this->complaint->test_item_report_serial_no ?: '');
        }

        // 1. Initialize CAPA Data (Action Plan)
        if ($this->resolution) {
            $this->acceptance = $this->resolution->findings;
            $this->root_cause = $this->resolution->root_cause_analysis;
            $this->action_taken = $this->resolution->action_taken;
            $this->issued_to = $this->resolution->issued_to;
            $this->issued_by = $this->resolution->issued_by;
            $this->date_issued = $this->resolution->date_issued ? $this->resolution->date_issued->format('Y-m-d') : '';
            $this->proposed_close_out_date = $this->resolution->proposed_close_out_date ? $this->resolution->proposed_close_out_date->format('Y-m-d') : ($this->resolution->corrective_action_date ? $this->resolution->corrective_action_date->format('Y-m-d') : '');
            $this->ref_clause = $this->resolution->ref_clause;
            $this->car_type = $this->resolution->car_type;
            $this->risk_level = $this->resolution->risk_level;
            $this->capa_identified_by = $this->resolution->capa_identified_by;
            $this->capa_identified_date = $this->resolution->capa_identified_date ? $this->resolution->capa_identified_date->format('Y-m-d') : '';
            
            // If essential CAPA is already filled out, mark as saved
            if (!empty($this->acceptance) && !empty($this->root_cause)) {
                $this->isCapaSaved = true;
            }
        }

        // 2. Initialize NCR/RCA Data (Root Cause)
        if ($this->capaRecord) {
            $this->details_of_non_conformance = $this->capaRecord->details_of_non_conformance;
            $this->identified_by = $this->capaRecord->identified_by;
            $this->ncr_identified_date = $this->capaRecord->ncr_identified_date ? $this->capaRecord->ncr_identified_date->format('Y-m-d') : '';
            $this->root_cause = $this->capaRecord->root_cause;
            $this->lab_no = $this->capaRecord->lab_no ?: $this->lab_no;
            $this->effectiveness_verified_by = $this->capaRecord->effectiveness_verified_by;
            $this->effectiveness_date = $this->capaRecord->effectiveness_date ? \Carbon\Carbon::parse($this->capaRecord->effectiveness_date)->format('Y-m-d') : '';

            $whys = $this->capaRecord->why_why_analysis ?? [];
            if (is_string($whys)) {
                $whys = json_decode($whys, true) ?? [];
            }
            $this->why_1 = $whys['why_1'] ?? '';
            $this->why_2 = $whys['why_2'] ?? '';
            $this->why_3 = $whys['why_3'] ?? '';
            $this->why_4 = $whys['why_4'] ?? '';
            $this->why_5 = $whys['why_5'] ?? '';
            $this->capa_corrective_action = $whys['corrective_action'] ?? '';
            $this->problem_statement = $whys['problem_statement'] ?? '';

            if (!empty($this->problem_statement) && !empty($this->why_1) && !empty($this->why_2)) {
                $this->isNcrSaved = true;
            }
        }

        // 3. Set default view based on progression
        // Load ncr_required from resolution FIRST
        if ($this->resolution) {
            $this->ncr_required = (bool) $this->resolution->ncr_required;
        }

        // Determine step_status
        if ($this->ncr_required) {
            if ($this->isCapaSaved) { 
                 $this->step_status = 'capa_completed';
            } elseif (!empty($this->capaRecord) && !empty($this->problem_statement)) {
                 $this->step_status = 'ncr_verification'; 
                 $this->activeView = 'capa'; // Force back to CAPA for step 3
            } elseif ($this->resolution && !empty($this->resolution->date_issued)) {
                 $this->step_status = 'ncr_in_progress';
                 $this->activeView = 'ncr'; // Force to NCR for step 2
            } else {
                 $this->step_status = 'ncr_pending_init';
                 $this->activeView = 'capa';
            }
        } else {
            // Normal fallback if NCR not required
            $this->step_status = 'ncr_pending_init';
            if ($this->isCapaSaved) {
                $this->step_status = 'capa_completed';
            }
        }
    }

    /**
     * React to NCR Required toggle change.
     */
    public function updatedNcrRequired()
    {
        if ($this->resolution) {
            $this->resolution->ncr_required = $this->ncr_required;
            $this->resolution->save();
        }
        
        // Recalculate status based on toggle
        if (!$this->ncr_required) {
            $this->step_status = 'ncr_pending_init';
            if ($this->isCapaSaved) {
                $this->step_status = 'capa_completed';
            }
            $this->activeView = 'capa';
        } else {
            // Re-evaluate based on existing data
            if ($this->isCapaSaved) { 
                 $this->step_status = 'capa_completed';
            } elseif (!empty($this->capaRecord) && !empty($this->problem_statement)) {
                 $this->step_status = 'ncr_verification'; 
            } elseif ($this->resolution && !empty($this->resolution->date_issued)) {
                 $this->step_status = 'ncr_in_progress';
            } else {
                 $this->step_status = 'ncr_pending_init';
            }
        }
    }

    public function switchView($view)
    {
        // When ncr_required=true, enforce NCR completion before CAPA
        if ($this->ncr_required) {
            if ($view === 'capa' && $this->step_status === 'ncr_in_progress') {
                $this->dispatch('alert', ['type' => 'warning', 'message' => 'Please complete the Why-Why (NCR) analysis first before filling the CAPA form.']);
                return;
            }
            if ($view === 'ncr' && $this->step_status === 'ncr_pending_init') {
                $this->dispatch('alert', ['type' => 'warning', 'message' => 'Please complete and save the CAPA initial setup first.']);
                return;
            }
        } else {
            // Original logic for non-NCR
            if ($view === 'ncr' && !$this->isCapaSaved) {
                $this->dispatch('alert', ['type' => 'warning', 'message' => 'Please complete and save the CAPA Action Plan first.']);
                return;
            }
        }
        $this->activeView = $view;
        $this->dispatch('view-switched', $view);
    }

    public function toggleEdit()
    {
        $this->checkPermission('crm.components.complaint verification.edit');
        $this->isEditing = true;
        $this->dispatch('edit-mode-activated');
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->mount($this->complaintId); // Re-load from DB to discard unsaved changes
        $this->dispatch('edit-mode-deactivated');
    }

    public function saveCapaInit()
    {
        $this->checkPermission('crm.components.complaint verification.edit');

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
            $this->resolution->workflow_stage = $this->complaint->complaint_workflow;
        }

        $this->resolution->issued_to = $this->issued_to;
        $this->resolution->issued_by = $this->issued_by;
        $this->resolution->date_issued = $this->date_issued ?: null;
        $this->resolution->proposed_close_out_date = $this->proposed_close_out_date ?: null;
        $this->resolution->ref_clause = $this->ref_clause;
        $this->resolution->car_type = $this->car_type;
        $this->resolution->risk_level = $this->risk_level;
        $this->resolution->save();
        
        // Save Details of Non conformance since moved to Section 2 of CAPA
        if (!$this->capaRecord) {
            $this->capaRecord = new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }
        $this->capaRecord->details_of_non_conformance = $this->details_of_non_conformance;
        $this->capaRecord->save();

        if ($this->ncr_required) {
            $this->step_status = 'ncr_in_progress';
            $this->activeView = 'ncr';
            $this->dispatch('view-switched', 'ncr');

            // Log to Chain of Custody
            $chain = new Chain_of_Custody_Complaint();
            $chain->complaint_id = $this->complaint->id;
            $chain->action = "CAPA Initialized. Moved to Why-Why Analysis.";
            $chain->action_taker_id = Auth::id();
            $chain->workflow_stage = getComplaintWorkflow()[$this->complaint->complaint_workflow] ?? "Stage " . $this->complaint->complaint_workflow;
            $chain->save();

            $this->dispatch('alert', ['type' => 'success', 'message' => 'CAPA Initial Setup saved. Please complete Root Cause Analysis.']);
        } else {
            $this->dispatch('alert', ['type' => 'success', 'message' => 'CAPA Initial Setup saved.']);
        }

        $this->isEditing = false;
    }

    public function approveCapaVerification()
    {
        $this->checkPermission('crm.components.complaint verification.edit');

        $this->validate([
            'acceptance' => 'required|string',
            'action_taken' => 'required|string',
        ], [
            'acceptance.required' => 'Please provide the acceptance of corrective action.',
            'action_taken.required' => 'Please provide the action taken.',
        ]);

        if ($this->ncr_required) {
             $this->validate([
                 'root_cause' => 'required|string',
                 'capa_corrective_action' => 'required|string',
             ], [
                 'root_cause.required' => 'Root cause must be defined in the NCR step.',
                 'capa_corrective_action.required' => 'Corrective Action must be defined in the NCR step.',
             ]);
        } else {
            // If NCR not required, we still need root cause from stage 1 probably
             $this->validate([
                 'root_cause' => 'required|string',
             ], [
                 'root_cause.required' => 'Please provide the root cause analysis.',
             ]);
        }

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
            $this->resolution->workflow_stage = $this->complaint->complaint_workflow;
        }

        $this->resolution->findings = $this->acceptance;
        $this->resolution->root_cause_analysis = $this->root_cause;
        $this->resolution->action_taken = $this->action_taken;
        // Keep Sec 1 & 2 in case changed
        $this->resolution->issued_to = $this->issued_to;
        $this->resolution->issued_by = $this->issued_by;
        $this->resolution->date_issued = $this->date_issued ?: null;
        $this->resolution->proposed_close_out_date = $this->proposed_close_out_date ?: null;
        $this->resolution->ref_clause = $this->ref_clause;
        $this->resolution->car_type = $this->car_type;
        $this->resolution->risk_level = $this->risk_level;
        $this->resolution->ncr_required = $this->ncr_required;
        $this->resolution->save();

        if ($this->capaRecord) {
            $this->capaRecord->root_cause = $this->root_cause;
            $this->capaRecord->effectiveness_verified_by = $this->effectiveness_verified_by;
            $this->capaRecord->effectiveness_date = $this->effectiveness_date ?: null;
            $whys = $this->capaRecord->why_why_analysis ?? [];
            if (is_string($whys)) {
                $whys = json_decode($whys, true) ?? [];
            }
            $whys['corrective_action'] = $this->capa_corrective_action;
            $this->capaRecord->why_why_analysis = $whys;
            $this->capaRecord->save();
        }

        $this->isCapaSaved = true;
        
        $this->step_status = 'capa_completed';

        // Advance to Stage 4 (Pending Closure)
        $nextStage = 4;
        $this->complaint->complaint_workflow = $nextStage;
        $this->complaint->save();

        if ($this->resolution) {
            $this->resolution->workflow_stage = $nextStage;
            $this->resolution->save();
        }

        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = "CAPA Approved & Finalized (Split Workflow)";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[$nextStage] ?? "Stage $nextStage";
        $chain->save();

        $this->showSuccess('CAPA Approved. Complaint has moved to Pending Closure.');
        $this->isEditing = false;
        $this->dispatch('complaint-workflow-updated');
    }

    public function saveDraftNcr()
    {
        $this->checkPermission('crm.components.complaint verification.edit');

        $this->saveNcrData();
        $this->dispatch('alert', ['type' => 'success', 'message' => 'NCR draft saved successfully.']);
    }

    public function saveNcrOnly()
    {
        $this->checkPermission('crm.components.complaint verification.edit');

        $this->validate([
            'problem_statement' => 'required|string',
            'why_1' => 'required|string',
            'why_2' => 'required|string',
        ]);

        $this->saveNcrData();
        $this->isNcrSaved = true;
        $this->isEditing = false;

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Why-Why Analysis saved successfully. You can now Return to CAPA.']);
    }

    public function returnToCapa()
    {
        $this->checkPermission('crm.components.complaint verification.edit');
        
        // Ensure NCR is saved before returning
        if (!$this->isNcrSaved) {
            $this->saveNcrOnly();
        }

        $this->step_status = 'ncr_verification';
        $this->activeView = 'capa';
        $this->dispatch('view-switched', 'capa');

        // Log to Chain of Custody
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = "Why-Why Analysis Completed. Returned to CAPA.";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[$this->complaint->complaint_workflow] ?? "Stage " . $this->complaint->complaint_workflow;
        $chain->save();
    }

    protected function saveNcrData()
    {
        if (!$this->capaRecord) {
            $this->capaRecord = new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }

        $this->capaRecord->details_of_non_conformance = $this->details_of_non_conformance;
        $this->capaRecord->identified_by = $this->identified_by;
        $this->capaRecord->ncr_identified_date = $this->ncr_identified_date ?: null;
        $this->capaRecord->root_cause = $this->root_cause;
        $this->capaRecord->lab_no = $this->lab_no;
        $this->capaRecord->effectiveness_verified_by = $this->effectiveness_verified_by;
        $this->capaRecord->effectiveness_date = $this->effectiveness_date ?: null;
        
        $this->capaRecord->why_why_analysis = [
            'why_1' => $this->why_1,
            'why_2' => $this->why_2,
            'why_3' => $this->why_3,
            'why_4' => $this->why_4,
            'why_5' => $this->why_5,
            'corrective_action' => $this->capa_corrective_action,
            'problem_statement' => $this->problem_statement,
        ];

        $this->capaRecord->save();
    }

    /**
     * Background autosave for all CAPA/NCR fields.
     * Skips strict validation and flash messages.
     */
    public function performAutosave()
    {
        if (!Auth::check()) return;

        // 1. Update Resolution
        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
            $this->resolution->workflow_stage = $this->complaint->complaint_workflow;
        }

        $this->resolution->issued_to = $this->issued_to;
        $this->resolution->issued_by = $this->issued_by;
        $this->resolution->date_issued = $this->date_issued ?: null;
        $this->resolution->proposed_close_out_date = $this->proposed_close_out_date ?: null;
        $this->resolution->ref_clause = $this->ref_clause;
        $this->resolution->car_type = $this->car_type;
        $this->resolution->risk_level = $this->risk_level;
        $this->resolution->ncr_required = $this->ncr_required;
        
        $this->resolution->findings = $this->acceptance;
        $this->resolution->root_cause_analysis = $this->root_cause;
        $this->resolution->action_taken = $this->action_taken;
        
        $this->resolution->save();

        // 2. Update CAPA Record (NCR specific)
        if (!$this->capaRecord) {
            $this->capaRecord = new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }

        $this->capaRecord->details_of_non_conformance = $this->details_of_non_conformance;
        $this->capaRecord->identified_by = $this->identified_by;
        $this->capaRecord->ncr_identified_date = $this->ncr_identified_date ?: null;
        $this->capaRecord->root_cause = $this->root_cause;
        $this->capaRecord->lab_no = $this->lab_no;
        $this->capaRecord->effectiveness_verified_by = $this->effectiveness_verified_by;
        $this->capaRecord->effectiveness_date = $this->effectiveness_date ?: null;
        
        $this->capaRecord->why_why_analysis = [
            'why_1' => $this->why_1,
            'why_2' => $this->why_2,
            'why_3' => $this->why_3,
            'why_4' => $this->why_4,
            'why_5' => $this->why_5,
            'problem_statement' => $this->problem_statement,
            'corrective_action' => $this->capa_corrective_action,
        ];

        $this->capaRecord->save();
        
        $this->dispatch('autosave-completed', ['time' => now()->format('H:i:s')]);
    }

    public function rejectCapa()
    {
        $this->checkPermission('crm.components.complaint verification.edit');
        
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
        $chain->workflow_stage = getComplaintWorkflow()[2] ?? "Stage 2";
        $chain->save();

        $this->showSuccess('CAPA rejected. Complaint returned to Active Investigations.');
        $this->dispatch('complaint-workflow-updated');
    }

    public function downloadCapaReport()
    {
        $this->checkPermission('crm.components.complaint verification.view');
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

    public function downloadNcrReport()
    {
        $this->checkPermission('crm.components.complaint verification.view');
        if (!$this->capaRecord) return;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.ncr_report', [
            'complaint' => $this->complaint,
            'resolution' => $this->resolution,
            'capaRecord' => $this->capaRecord
        ]);

        $safeId = str_replace(['/', '\\'], '-', ($this->capaRecord->lab_no ?: $this->complaint->complaint_id));
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'NCR_Report_' . $safeId . '.pdf');
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-capa-tab');
    }
}
