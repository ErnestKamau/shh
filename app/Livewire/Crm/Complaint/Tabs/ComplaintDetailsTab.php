<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\Chain_of_Custody_Complaint;

class ComplaintDetailsTab extends BaseCrmComponent
{
    public $complaint;
    public $workflowStage;

    public function mount($complaint)
    {
        $this->complaint = $complaint;
        
        // Determine workflow stage
        $complaint_workflow = getComplaintsWorkFlowValues();
        foreach ($complaint_workflow as $x => $x_value) {
            if ($x_value == $this->complaint->complaint_workflow) {
                $this->workflowStage = $x;
            }
        }
    }


    /**
     * Get the officer who closed this complaint (from chain of custody "Report Sent & Complaint Closed" action).
     */
    public function getClosureOfficerProperty()
    {
        $entry = Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
            ->where('action', 'Report Sent & Complaint Closed')
            ->with('actionTaker')
            ->orderByDesc('id')
            ->first();

        return $entry?->actionTaker?->name ?? null;
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-details-tab', [
            'complaint' => $this->complaint,
            'workflowStage' => $this->workflowStage,
            'closureOfficer' => $this->closureOfficer,
        ]);
    }
}
