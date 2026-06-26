<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\CapaRecord;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class ComplaintNcTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $capaRecord;
    public $resolution;

    public $isEditing = false;
    
    // NC Fields

    // RCA / Why-Why Fields (Migrated from CAPA)
    public $problem_statement = '';
    public array $whys = []; 
    public $root_cause_analysis = '';
    public $root_cause_by = [];
    public $root_cause_date = '';
    public $corrective_action_taken = '';
    public $corrective_action_by = [];
    public $corrective_action_date = '';

    public string $nc_status = 'DRAFT';

    public function mount($complaintId, $isEditing = false)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->isEditing = $isEditing;
        $this->complaint = Complaint::findOrFail($complaintId);
        
        $this->capaRecord = CapaRecord::where('complaint_id', $this->complaintId)->first();
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();
 
        if ($this->capaRecord) {
            // Restore RCA Data
            $whys_analysis = $this->capaRecord->why_why_analysis ?? [];
            if (is_string($whys_analysis)) {
                $whys_analysis = json_decode($whys_analysis, true) ?? [];
            }
            $this->whys = $whys_analysis['whys'] ?? [];
            $this->problem_statement = $whys_analysis['problem_statement'] ?? '';
            
            // Restore Metadata
            $this->root_cause_analysis = $whys_analysis['root_cause_analysis'] ?? '';
            $this->root_cause_by = isset($whys_analysis['root_cause_by']) && !empty($whys_analysis['root_cause_by']) 
                ? array_map('trim', explode(',', $whys_analysis['root_cause_by'])) 
                : [];
            $this->root_cause_date = $whys_analysis['root_cause_date'] ?? '';
            
            $this->corrective_action_taken = $whys_analysis['corrective_action_taken'] ?? '';
            $this->corrective_action_by = isset($whys_analysis['corrective_action_by']) && !empty($whys_analysis['corrective_action_by']) 
                ? array_map('trim', explode(',', $whys_analysis['corrective_action_by'])) 
                : [];
            $this->corrective_action_date = $whys_analysis['corrective_action_date'] ?? '';

            // Backwards compatibility for old why_1, why_2 if they exist in JSON
            if (empty($this->whys)) {
                if (isset($whys_analysis['why_1'])) $this->whys[] = $whys_analysis['why_1'];
                if (isset($whys_analysis['why_2'])) $this->whys[] = $whys_analysis['why_2'];
            }
        }

        if (empty($this->whys)) {
            $this->whys = ['']; // At least one why to start with
        }

        // Problem statement is NOT auto-populated from complaint details.
        // Users must enter it manually for a deliberate NC analysis.

        // We specifically do NOT pull root_cause or corrective_action from $this->resolution 
        // to prevent autopicking from Investigation phase as requested.
        // NC now relies on its own independent storage in capaRecord JSON.
        if (empty($this->root_cause_date)) {
             $this->root_cause_date = now()->format('Y-m-d');
        }
        if (empty($this->corrective_action_date)) {
             $this->corrective_action_date = now()->format('Y-m-d');
        }

        $this->deriveNcStatus();
    }

    public function getNextPendingActionProperty()
    {
        $cleanProblem = trim(strip_tags((string)$this->problem_statement));
        $allWhysEmpty = true;
        foreach($this->whys as $why) {
            if (!empty(trim(strip_tags((string)$why)))) {
                $allWhysEmpty = false;
                break;
            }
        }

        if (empty($cleanProblem)) return "Define Problem Statement";
        if ($allWhysEmpty) return "Perform 5-Why Analysis";
        if (empty($this->root_cause_analysis)) return "Identify Root Cause";
        if (empty($this->corrective_action_taken)) return "Propose Corrective Action";
        
        return "NC Investigation Complete";
    }

    protected function deriveNcStatus()
    {
        $cleanProblem = trim(strip_tags((string)$this->problem_statement));
        $allWhysEmpty = true;
        foreach($this->whys as $why) {
            if (!empty(trim(strip_tags((string)$why)))) {
                $allWhysEmpty = false;
                break;
            }
        }

        if (empty($cleanProblem) && $allWhysEmpty) {
            $this->nc_status = 'DRAFT';
        } elseif (empty($cleanProblem) || $allWhysEmpty) {
            $this->nc_status = 'IN-PROGRESS';
        } else {
            $this->nc_status = 'ANALYZED';
        }
    }

    public function getNcButtonLabelProperty()
    {
        return !$this->capaRecord 
            ? 'Record Non-Conformance' 
            : 'Edit Non-Conformance';
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
        if ($action === 'add_nc') {
            $this->isEditing = true;
        } elseif ($action === 'toggle_edit') {
            $this->toggleEdit();
        }
    }

    public function toggleEdit()
    {
        $this->isEditing = !$this->isEditing;
        if (!$this->isEditing) {
            $this->mount($this->complaint->id);
        }
    }

    public function addWhy()
    {
        $this->whys[] = '';
        $this->dispatch('why-added');
    }

    public function removeWhy($index)
    {
        if (isset($this->whys[$index])) {
            unset($this->whys[$index]);
            $this->whys = array_values($this->whys);
        }
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

    public function saveNcDetails(array $richTextData = [])
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        
        if (!empty($richTextData)) {
            $this->applyRichTextData($richTextData);
        }
        
        $this->validate([
            'problem_statement' => 'required',
            'whys.*' => 'required',
            'root_cause_analysis' => 'required',
            'root_cause_by' => 'required|array|min:1',
            'root_cause_date' => 'required',
            'corrective_action_taken' => 'required',
            'corrective_action_by' => 'required|array|min:1',
            'corrective_action_date' => 'required',
        ], [
            'whys.*.required' => 'All Why analysis fields are required.',
            'root_cause_by.required' => 'The root cause identified by field is required.',
            'root_cause_date.required' => 'The root cause date field is required.',
            'corrective_action_by.required' => 'The corrective action identified by field is required.',
            'corrective_action_date.required' => 'The corrective action date field is required.',
        ]);
 
        if (!$this->capaRecord) {
            $this->capaRecord = $this->complaint->ncr_details ?: new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }
 
        // Save RCA Data (Synchronize with resolution but store structured for NCR report)
        $this->capaRecord->why_why_analysis = [
            'whys' => $this->whys,
            'problem_statement' => $this->problem_statement,
            'root_cause_analysis' => $this->root_cause_analysis,
            'root_cause_by' => is_array($this->root_cause_by) ? implode(', ', $this->root_cause_by) : $this->root_cause_by,
            'root_cause_date' => $this->root_cause_date,
            'corrective_action_taken' => $this->corrective_action_taken,
            'corrective_action_by' => is_array($this->corrective_action_by) ? implode(', ', $this->corrective_action_by) : $this->corrective_action_by,
            'corrective_action_date' => $this->corrective_action_date,
        ];
        
        $this->capaRecord->save();
        $this->deriveNcStatus();

        // Note: We are no longer syncing CA/RCA to resolution here 
        // to prevent autopicking in CAPA as requested.
        if ($this->resolution) {
            $this->resolution->save();
        }
 
        $this->isEditing = false;
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Non-conformance & RCA details recorded. Proceeding to CAPA.']);
        $this->dispatch('nc-completed');
    }

    #[On('save-investigation-draft')]
    public function persistDraft()
    {
        // When parent triggers save-as-draft, we bypass validation to allow partial saves
        if (!$this->isEditing) return;

        if (!$this->capaRecord) {
            $this->capaRecord = CapaRecord::where('complaint_id', $this->complaint->id)->first() ?: new CapaRecord();
            $this->capaRecord->complaint_id = $this->complaint->id;
        }

        $this->capaRecord->why_why_analysis = [
            'whys' => $this->whys,
            'problem_statement' => $this->problem_statement,
            'root_cause_analysis' => $this->root_cause_analysis,
            'root_cause_by' => is_array($this->root_cause_by) ? implode(', ', $this->root_cause_by) : $this->root_cause_by,
            'root_cause_date' => $this->root_cause_date,
            'corrective_action_taken' => $this->corrective_action_taken,
            'corrective_action_by' => is_array($this->corrective_action_by) ? implode(', ', $this->corrective_action_by) : $this->corrective_action_by,
            'corrective_action_date' => $this->corrective_action_date,
        ];
        
        $this->capaRecord->save();

        if ($this->resolution) {
            $this->resolution->save();
        }

        $this->deriveNcStatus();
    }

    public function downloadNcrReport()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
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
        return view('livewire.crm.complaint.tabs.complaint-nc-tab', [
            'users' => getAllUsers()
        ]);
    }
}
