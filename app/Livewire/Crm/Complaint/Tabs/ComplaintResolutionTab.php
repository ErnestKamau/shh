<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use App\Exports\CRM\ComplaintTabExport;

class ComplaintResolutionTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $resolutions;
    public $editingResolutionId = null;
    public $action = '';
    public $findings = '';
    public $root_cause_analysis = '';
    public $corrective_action = '';
    public $preventive_action = '';
    public $officer_responsible = '';
    public $resolved_by_user_id = '';
    public $users = [];
    public $activeTab = 'findings';

    public $selectedResolution;

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::findOrFail($complaintId);
        $this->loadResolutions();
        $this->users = getAllUsers();
    }

    public function loadResolutions()
    {
        $this->resolutions = Complaintsresolutions::where('complaint_id', $this->complaintId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new ComplaintTabExport($this->complaintId, 'resolutions'))->download('complaint_resolutions_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function viewResolution($id)
    {
        $this->selectedResolution = Complaintsresolutions::find($id);
        $this->dispatch('show-view-resolution-modal', [
            'car_no' => $this->selectedResolution->car_no,
            'action' => $this->selectedResolution->action_taken,
            'findings' => $this->selectedResolution->findings,
            'root_cause_analysis' => $this->selectedResolution->root_cause_analysis,
            'corrective_action' => $this->selectedResolution->corrective_action_taken,
            'preventive_action' => $this->selectedResolution->preventive_action,
            'officer_responsible' => $this->selectedResolution->officer_responsible,
            'registered_by' => $this->selectedResolution->registered_by,
            'created_at' => $this->selectedResolution->created_at->format('Y-m-d H:i'),
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function openResolutionModal($id = null)
    {
        $this->resetValidation();
        $this->activeTab = 'findings';
        
        if ($id) {
            $resolution = Complaintsresolutions::find($id);
            if ($resolution) {
                $this->editingResolutionId = $id;
                $this->action = $resolution->action_taken;
                $this->findings = $resolution->findings;

                // Fallback for legacy data: if findings is empty but action has content (and isn't the generic placeholder)
                // we treat action as the findings.
                if (empty($this->findings) && !empty($this->action) && $this->action !== 'See detailed resolution fields.') {
                    $this->findings = $this->action;
                }

                $this->root_cause_analysis = $resolution->root_cause_analysis;
                $this->corrective_action = $resolution->corrective_action_taken;
                $this->preventive_action = $resolution->preventive_action;
                $this->officer_responsible = $resolution->officer_responsible;
                $this->resolved_by_user_id = $resolution->resolved_by_user_id;
                
                // Dispatch with data for editing
                $this->dispatch('show-resolution-modal', [
                    'id' => $id,
                    'action' => $this->action,
                    'findings' => $this->findings,
                    'root_cause_analysis' => $this->root_cause_analysis,
                    'corrective_action' => $this->corrective_action,
                    'preventive_action' => $this->preventive_action,
                    'officer' => $this->resolved_by_user_id, // Send ID for Select2
                ]);
                return;
            }
        }
        
        // New resolution
        $this->editingResolutionId = null;
        $this->action = '';
        $this->findings = '';
        $this->root_cause_analysis = '';
        $this->corrective_action = '';
        $this->preventive_action = '';
        $this->officer_responsible = '';
        $this->resolved_by_user_id = [];
        $this->dispatch('show-resolution-modal', []);
    }

    public function saveResolution($formData = [])
    {
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_RESOLUTION_ADD);

        // If formData is provided, use it to populate properties
        if (!empty($formData)) {
            $this->findings = $formData['findings'] ?? $this->findings;
            $this->root_cause_analysis = $formData['root_cause_analysis'] ?? $this->root_cause_analysis;
            $this->corrective_action = $formData['corrective_action'] ?? $this->corrective_action;
            $this->preventive_action = $formData['preventive_action'] ?? $this->preventive_action;
            $this->resolved_by_user_id = $formData['resolved_by_user_id'] ?? $this->resolved_by_user_id;
        }

        $this->validate([
            // 'action' => 'required|string', // Deprecated
            'findings' => 'required|string',
            'root_cause_analysis' => 'required|string',
            'corrective_action' => 'required|string',
            'preventive_action' => 'required|string',
            'resolved_by_user_id' => 'required|array|min:1',
        ]);

        if ($this->editingResolutionId) {
            $resolution = Complaintsresolutions::find($this->editingResolutionId);
            $resolution->edited_by = auth()->user()->name;
        } else {
            $resolution = new Complaintsresolutions();

            // Generate CAR number using str_pad
            // Pattern: [COMPLAINT_ID]RES[000X]
            $resolutions_count = Complaintsresolutions::where('complaint_id', $this->complaint->id)->count() + 1;
            $resolution->car_no = $this->complaint->complaint_id . "RES" . str_pad($resolutions_count, 4, '0', STR_PAD_LEFT);

            $resolution->complaint_id = $this->complaint->id;
            $resolution->workflow_stage = $this->complaint->complaint_workflow;
            $resolution->registered_by = auth()->user()->name;
        }

        // Concatenate for backward compatibility or leave empty/generic
        $resolution->action_taken = "See detailed resolution fields."; 
        $resolution->findings = $this->findings;
        $resolution->root_cause_analysis = $this->root_cause_analysis;
        $resolution->corrective_action_taken = $this->corrective_action;
        $resolution->preventive_action = $this->preventive_action;

        // Handle multiple officers
        $userIds = is_array($this->resolved_by_user_id) ? $this->resolved_by_user_id : [$this->resolved_by_user_id];
        $selectedUsers = \App\User::whereIn('id', $userIds)->get();
        
        $resolution->officer_responsible = $selectedUsers->pluck('name')->implode(', ');
        $resolution->resolved_by_user_id = $userIds[0] ?? null;

        $resolution->save();

        // Create chain of custody entry
        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $this->complaint->id;
        $chain_custody->action = $this->editingResolutionId ? "Updated resolution" : CrmConstants::ACTION_CREATE_RESOLUTION;
        $chain_custody->action_taker_id = auth()->user()->id;
        $chain_custody->workflow_stage = getComplaintWorkflow()[$this->complaint->complaint_workflow] ?? $this->complaint->complaint_workflow;
        $chain_custody->save();

        $this->loadResolutions();
        $this->showSuccess($this->editingResolutionId ? 'Resolution updated successfully' : 'Resolution saved successfully');
        $this->dispatch('close-resolution-modal');
        $this->dispatch('resolution-saved');
        
        $this->reset(['editingResolutionId', 'action', 'findings', 'root_cause_analysis', 'corrective_action', 'preventive_action', 'officer_responsible', 'resolved_by_user_id']);
    }

    #[On('resolution-saved')]
    public function refreshResolution()
    {
        $this->loadResolutions();
    }

    /**
     * Sanitize HTML for safe display in View modal (XSS prevention).
     * Allows tags used by TinyMCE toolbar: bold, italic, underline, lists, link.
     */
    public static function sanitizeResolutionHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }
        $allowed = '<p><br><strong><em><u><ul><ol><li><a><b><i>';
        return strip_tags($html, $allowed);
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-resolution-tab', [
            'resolutions' => $this->resolutions,
            'selectedResolution' => $this->selectedResolution,
        ]);
    }
}
 