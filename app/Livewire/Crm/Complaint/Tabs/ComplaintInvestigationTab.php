<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CRM\Complaintattachment;

class ComplaintInvestigationTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $resolution;

    // Form fields
    public $cause_of_complaint;
    public $action_taken; 
    public $action_taken_date;
    public $corrective_action_taken;
    public $corrective_action_date;
    public $car_required = false;

    // Modal state
    public $activeModalTab = 'cause';

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::findOrFail($complaintId);
        
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();
        
        if ($this->resolution) {
            $this->cause_of_complaint = $this->resolution->cause_of_complaint;
            $this->action_taken = $this->resolution->action_taken;
            $this->action_taken_date = $this->resolution->action_taken_date?->format('Y-m-d');
            $this->corrective_action_taken = $this->resolution->corrective_action_taken;
            $this->corrective_action_date = $this->resolution->corrective_action_date?->format('Y-m-d');
            $this->car_required = $this->resolution->car_required;
        } else {
            $this->action_taken_date = now()->format('Y-m-d');
            $this->corrective_action_date = now()->format('Y-m-d');
        }
    }

    public function saveInvestigation()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');

        $this->validate([
            'cause_of_complaint' => 'required|string',
            'action_taken' => 'required|string',
            'action_taken_date' => 'required|date',
            'corrective_action_taken' => 'required|string',
            'corrective_action_date' => 'required|date',
        ], [
            'cause_of_complaint.required' => 'Please detail the nature and cause of the complaint.',
            'action_taken.required' => 'Please provide the immediate action taken.',
            'corrective_action_taken.required' => 'Please provide the corrective action taken.',
        ]);

        if (!$this->resolution) {
            $this->resolution = new Complaintsresolutions();
            $this->resolution->complaint_id = $this->complaint->id;
            $this->resolution->registered_by = Auth::user()?->name;
        }

        $this->resolution->cause_of_complaint = $this->cause_of_complaint;
        $this->resolution->action_taken = $this->action_taken;
        $this->resolution->action_taken_date = $this->action_taken_date;
        $this->resolution->action_taken_by = Auth::id(); // Automatic signature
        $this->resolution->corrective_action_taken = $this->corrective_action_taken;
        $this->resolution->corrective_action_date = $this->corrective_action_date;
        $this->resolution->corrective_action_by = Auth::id(); // Automatic signature
        $this->resolution->car_required = $this->car_required;

        // Routing Logic based on CAR
        if ($this->car_required) {
            // YES CAR Required -> Generate CAR Number, route to Stage 3
            if (!$this->resolution->car_no) {
                // Generate logic: COMPLAINT_ID + CAR + sequence
                $resolutions_count = Complaintsresolutions::where('complaint_id', $this->complaint->id)->count() + 1;
                $this->resolution->car_no = $this->complaint->complaint_id . "CAR" . $resolutions_count;
            }
            $nextStage = 3; // Verification Review & CAPA
            $actionText = 'Investigation completed and actions logged by ' . (Auth::user()?->name ?? 'System') . '. Corrective Action Request (CAR) initiated.';
        } else {
            // NO CAR Required -> Route to Stage 4 (Pending Closure)
            $nextStage = 4; // Pending Closure / Resolution Approval
            $actionText = 'Investigation completed and actions logged by ' . (Auth::user()?->name ?? 'System') . '.';
            $this->resolution->car_no = null;
        }
        
        $this->resolution->workflow_stage = $nextStage;
        $this->resolution->save();

        // --- NEW: Generate and Attach Investigation Report PDF ---
        try {
            // Re-load models for PDF data consistency
            $complaint = Complaint::with(['client'])->find($this->complaint->id);
            $resolution = $this->resolution;
            $chainOfCustody = Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)->with('actionTaker')->get();

            $pdf = Pdf::loadView('pdfs.investigation_report', compact('complaint', 'resolution', 'chainOfCustody'));
            
            $safeId = str_replace(['/', '\\'], '-', $complaint->complaint_id);
            $fileName = 'Investigation_Report_' . $safeId . '_' . now()->format('YmdHis') . '.pdf';
            $storagePath = 'complaints/investigations/' . $fileName;
            
            Storage::disk('public')->put($storagePath, $pdf->output());

            // Create Attachment Entry
            $attachment = new Complaintattachment();
            $attachment->complaint_id = $this->complaint->id;
            $attachment->title = 'Laboratory Investigation Report';
            $attachment->type = 'Investigation Report';
            $attachment->description = 'Automatically generated investigation report after submission.';
            $attachment->file_path = '/storage/' . $storagePath;
            $attachment->posted_by = Auth::user()?->name;
            $attachment->is_public = true;
            $attachment->save();

        } catch (\Exception $e) {
            // Log error but don't block the workflow if PDF fails
            Log::error('Failed to generate Investigation Report PDF: ' . $e->getMessage());
        }
        // --------------------------------------------------------

        // Update complaint workflow
        $this->complaint->complaint_workflow = $nextStage;
        $this->complaint->save();

        // Log to Chain of Custody
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = $actionText;
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = getComplaintWorkflow()[$nextStage] ?? "Stage $nextStage";
        $chain->save();

        $this->showSuccess('Investigation details saved and report generated.');
        $this->dispatch('close-investigation-modal');
        $this->dispatch('complaint-workflow-updated');
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

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-investigation-tab');
    }
}
