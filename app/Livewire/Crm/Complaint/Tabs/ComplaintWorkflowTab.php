<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\CustomerContact;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Crm\BaseCrmComponent;
use App\Exports\CRM\ComplaintTabExport;

class ComplaintWorkflowTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $workflowStages;
    public $perPage = 10;
    public $comment = '';
    public $car_required = 0; // 0 = No, 1 = Yes
    public $viewMode = 'tab'; // 'tab' or 'modal'

    // Modal State
    public $modalAction = null;
    public $modalTitle = '';
    public $confirmButtonText = 'Submit';
    public $confirmButtonColor = 'btn-primary';
    public $selectedContactIds = [];


    public function mount($complaintId, $viewMode = 'tab')
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->viewMode = $viewMode;
        $this->complaint = Complaint::findOrFail($complaintId);
        $this->workflowStages = getComplaintWorkflowStages();
    }

    public function getChainOfCustodyProperty()
    {
        return Chain_of_Custody_Complaint::with(['complaint', 'actionTaker'])
            ->where('complaint_id', $this->complaintId)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function exportToExcel()
    {
        $this->checkPermission('CRM.permission');
        return (new ComplaintTabExport($this->complaintId, 'workflow'))->download('complaint_workflow_' . now()->format('Ymd_His') . '.xlsx');
    }

    #[On('initiate-workflow-action')]
    public function initiateAction($action)
    {
        // Handle potential object/array dispatch from Livewire v3 $dispatch('event', { key: 'val' })
        if (is_array($action)) {
            $action = $action['action'] ?? (isset($action[0]) ? $action[0] : '');
        }
        
        // Ensure it's a string to prevent htmlspecialchars errors in blade
        $this->modalAction = (string) $action;

        // Only the modal handler instance should respond to this event
        if ($this->viewMode !== 'modal') {
            return;
        }

        $this->comment = ''; // Clear previous comment
        $this->selectedContactIds = [];
        $this->car_required = 0; // Default to NO

        // Pre-fill car_required if we already have a resolution record
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if ($resolution) {
            $this->car_required = $resolution->car_required ? 1 : 0;
        }

        switch ($action) {
            case 'approveNext':
                $this->modalTitle = 'Approve Complaint';
                $this->confirmButtonText = 'Approve & Proceed';
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'reject':
                $this->modalTitle = 'Cancel Complaint';
                $this->confirmButtonText = 'Cancel Complaint';
                $this->confirmButtonColor = 'btn-danger';
                break;
            case 'reverseApproval':
                $this->modalTitle = 'Return Complaint';
                $this->confirmButtonText = 'Return to Previous Stage';
                $this->confirmButtonColor = 'btn-warning';
                break;
            case 'approveCapa':
                $this->modalTitle = 'Approve CAPA & Move to Pending Closure';
                $this->confirmButtonText = 'Approve CAPA';
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'closeComplaint':
                $this->modalTitle = 'Close Complaint';
                $this->confirmButtonText = 'Close Complaint';
                $this->confirmButtonColor = 'btn-primary';
                break;
            case 'requestResolutionApprove':
                $this->modalTitle = 'Request Resolution Approval';
                $this->confirmButtonText = 'Request Approval';
                $this->confirmButtonColor = 'btn-secondary';
                break;
            case 'approveResolution':
                $this->modalTitle = 'Approve Resolution';
                $this->confirmButtonText = 'Approve & Generate Report';
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'sendReportAndClose':
                $this->modalTitle = 'Send Report & Close Complaint';
                $this->confirmButtonText = 'Send & Close';
                $this->confirmButtonColor = 'btn-primary';
                break;
            case 'rejectResolution':
                $this->modalTitle = 'Cancel Resolution';
                $this->confirmButtonText = 'Cancel';
                $this->confirmButtonColor = 'btn-danger';
                break;
            case 'reverseResolution':
                $this->modalTitle = 'Return Resolution';
                $this->confirmButtonText = 'Return Resolution';
                $this->confirmButtonColor = 'btn-warning';
                break;
            case 'logForRecordOnly':
                $this->modalTitle = 'Log for Record Only';
                $this->confirmButtonText = 'Log & Close';
                $this->confirmButtonColor = 'btn-outline-secondary';
                break;
            case 'regenerateReport':
                $this->modalTitle = 'Regenerate Closure Report';
                $this->confirmButtonText = 'Regenerate & Update';
                $this->confirmButtonColor = 'btn-outline-primary';
                break;
        }

        $this->dispatch('show-action-modal');
    }

    public function performAction($comment = null)
    {
        if ($comment !== null) {
            $this->comment = $comment;
        }

        if (method_exists($this, $this->modalAction)) {
            $this->{$this->modalAction}();
        }
        
        $this->dispatch('close-action-modal');
        $this->modalAction = null;
    }

    public function approveNext()
    {
        $current_stage = $this->complaint->complaint_workflow;
        
        // Permission depends on the stage we are moving FROM
        $permissionMap = [
            1 => 'CRM.components.Open Complaint.Edit',
            2 => 'CRM.components.Complaint Investigation.Edit',
            3 => 'CRM.components.Complaint Verification.Edit',
            4 => 'CRM.components.Complaint Pending Closure.Edit',
        ];
        
        $perm = $permissionMap[$current_stage] ?? 'CRM.components.Complaint Investigation.Edit';
        $this->checkPermission($perm);
        
        $current_stage = $this->complaint->complaint_workflow;
        $workflow_stages = getComplaintsWorkFlowValues();
        
        // DEFAULT: Move to Stage 2
        $next_stage_value = $current_stage + 1;
        
        // SPECIAL ROUTING for Stage 1 -> Stage 3 skip
        if ($current_stage == 1 && $this->car_required) {
            $next_stage_value = 3; // Jump to Complaint Verification / CAPA
        }

        if ($next_stage_value == 5) {
            $this->complaint->edited_by = Auth::user()->name;
        }

        // Stamp intake approval and save CAR decision when moving from Stage 1 (Open Complaint)
        if ($current_stage == 1) {
            $this->complaint->intake_approved_by = Auth::id();
            $this->complaint->intake_approved_at = now();

            // Find or create resolution to store CAR requirement
            $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first() 
                ?: new \App\Models\CRM\Complaintsresolutions();
            
            $resolution->complaint_id = $this->complaint->id;
            $resolution->car_required = (bool) $this->car_required;
            if (!$resolution->registered_by) {
                $resolution->registered_by = Auth::user()?->name;
            }
            
            // Generate CAR Number if required and missing (e.g. bypassing Stage 2)
            if ($resolution->car_required && empty($resolution->car_no)) {
                $resolutions_count = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->count();
                if (!$resolution->exists) {
                    $resolutions_count++;
                }
                $resolution->car_no = $this->complaint->complaint_id . "CAR" . str_pad(max(1, $resolutions_count), 4, '0', STR_PAD_LEFT);
            }

            $resolution->save();
        }
        
        $this->complaint->complaint_workflow = $next_stage_value;
        $this->complaint->save();

        $chain_custody = new Chain_of_Custody_Complaint();
        $approval_actions = getComplaintsActionsApproval();
        $action = $approval_actions[$current_stage] ?? 'Approved';

        $chain_custody->complaint_id = $this->complaint->id;
        
        // Dynamic action text based on destination
        $action = $approval_actions[$current_stage] ?? 'Approved';
        if ($current_stage == 1) {
            $action = $this->car_required ? 'Approve & Move to CAPA' : 'Approve & Move to Active Investigation';
        }

        $chain_custody->action = $action;
        $chain_custody->action_taker_id = Auth::id();
        $chain_custody->workflow_stage = $this->getStageName($current_stage); 
        $chain_custody->comments = $this->comment;
        $chain_custody->move_out_date = getTodayDate();
        $chain_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint approved successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'This complaint has moved to the next stage.');
    }

    public function logForRecordOnly()
    {
        $this->checkPermission('CRM.components.Complaints Approval.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->complaint_workflow = 5; // Move straight to Closed
        $this->complaint->is_closed = true;
        $this->complaint->closed_by = Auth::id();
        $this->complaint->date_closed = now();
        $this->complaint->save();

        // Create chain of custody
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = "Logged for Record Only & Closed";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($current_stage);
        $chain->comments = $this->comment;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint has been logged for record only and closed.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint has been logged for record only and closed.');
    }

    public function reject()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Delete');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->rejected = 1;
        $this->complaint->complaint_workflow = 6;
        $this->complaint->reject_workflow = $current_stage;
        $this->complaint->save();

        $new_chain = new Chain_of_Custody_Complaint();
        $new_chain->complaint_id = $this->complaint->id;
        $new_chain->action = "Reject Complaint";
        $new_chain->action_taker_id = Auth::id();
        // Updated: Use the helper to save text instead of number
        $new_chain->workflow_stage = $this->getStageName($this->complaint->complaint_workflow); 
        $new_chain->comments = $this->comment;
        $new_chain->move_out_date = getTodayDate();
        $new_chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint rejected successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'The complaint has been rejected.');
    }

    public function reverseApproval()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Delete');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->complaint_workflow = $current_stage - 1;
        $this->complaint->save();

        $new_custody = new Chain_of_Custody_Complaint();
        $reverse_actions = getComplaintActionReverse();
        $action = $reverse_actions[$current_stage] ?? 'Reverse Approval';

        $new_custody->complaint_id = $this->complaint->id;
        $new_custody->action = $action;
        $new_custody->action_taker_id = Auth::id();
        // Updated: Use the helper to save text instead of number
        $new_custody->workflow_stage = $this->getStageName($current_stage); 
        $new_custody->comments = $this->comment;
        $new_custody->move_out_date = getTodayDate();
        $new_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint returned successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'The complaint has been returned to the previous stage.');
    }

    public function requestResolutionApprove()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Add');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->complaint_workflow = 4;
        $this->complaint->save();

        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $this->complaint->id;
        $chain_custody->action = "Request Resolution Approval";
        $chain_custody->action_taker_id = Auth::id();
        // Updated: Use the helper to save text instead of number
        $chain_custody->workflow_stage = $this->getStageName($current_stage); 
        $chain_custody->comments = $this->comment;
        $chain_custody->move_out_date = getTodayDate();
        $chain_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution approval requested successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution approval requested successfully.');
    }

    public function approveResolution()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;

        // 1. Generate & Save Report (extracted method)
        $this->generateClosureReport();

        // 2. Log Action
        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $this->complaint->id;
        $chain_custody->action = "Resolution Approved & Report Generated";
        $chain_custody->action_taker_id = Auth::id();
        $chain_custody->workflow_stage = $this->getStageName($current_stage); 
        $chain_custody->comments = $this->comment;
        $chain_custody->move_out_date = getTodayDate();
        $chain_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution approved successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution approved and report generated.');
    }

    public function regenerateReport()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;

        // 1. Generate & Save Report (extracted method)
        $this->generateClosureReport();

        // 2. Do NOT log to chain of custody - report regeneration is not a meaningful workflow step
        $this->complaint->refresh();   

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Closure report regenerated successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Closure report regenerated successfully.');
    }

    private function generateClosureReport()
    {
        try {
            $complaintForReport = Complaint::with(['client', 'resolutions'])->find($this->complaint->id);
            $resolution = $complaintForReport->resolutions->last();
            $capaRecord = \App\Models\CRM\CapaRecord::where('complaint_id', $this->complaint->id)->first();
            
            $chainOfCustodyForReport = Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
                ->whereNotIn('action', ['Closure Report Regenerated'])
                ->orderBy('created_at', 'asc')
                ->get();
            
            $publicNotes = $complaintForReport->notes()
                ->where('is_public', 1)
                ->where('is_delete', '!=', 1)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $publicAttachments = $complaintForReport->attachments()
                ->where('is_public', 1)
                ->where('is_delete', '!=', 1)
                ->where(function($query) {
                    $query->where('type', '!=', 'Closure Report')
                          ->where('title', '!=', 'Closure Report')
                          ->where('type', '!=', 'Investigation Report')
                          ->where('title', '!=', 'Investigation Report');
                })
                ->orderBy('created_at', 'asc')
                ->get();

            $reportConfig = [
                'complaint' => $complaintForReport,
                'resolution' => $resolution,
                'capaRecord' => $capaRecord,
                'chainOfCustody' => $chainOfCustodyForReport,
                'publicNotes' => $publicNotes,
                'publicAttachments' => $publicAttachments,
            ];

            // 1. ALWAYS Generate Investigation Report
            $this->savePdfReport('pdfs.investigation_report', 'Investigation Report', $reportConfig);

            // 2. Generate CAPA/Closure Report IF CAR Required
            if ($resolution && $resolution->car_required) {
                $this->savePdfReport('pdfs.closure_report', 'Closure Report', $reportConfig);
            }

            $this->dispatch('alert', ['type' => 'success', 'message' => 'Reports generated successfully.']);

       } catch (\Exception $e) {
           \Illuminate\Support\Facades\Log::error("Failed to generate closure reports: " . $e->getMessage());
           $this->dispatch('alert', ['type' => 'error', 'message' => 'Failed to generate reports: ' . $e->getMessage()]);
           throw $e;
       }
    }

    /**
     * Helper to render and save a PDF report as an attachment.
     */
    private function savePdfReport($template, $title, $data)
    {
        $html = view($template, $data)->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        if ($template === 'pdfs.closure_report') {
            \addClosureReportPageNumbers($pdf);
        }
        $pdfContent = $pdf->output();

        $snakeTitle = str_replace(' ', '_', $title);
        $fileName = $snakeTitle . '_' . $this->complaint->id . '.pdf';
        $storagePath = 'complaints/' . $this->complaint->id . '/' . $fileName;
        
        \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $pdfContent);
        
        $attachment = \App\Models\CRM\Complaintattachment::where('complaint_id', $this->complaint->id)
            ->where('title', $title)
            ->where('type', $title)
            ->first() ?: new \App\Models\CRM\Complaintattachment();

        $attachment->complaint_id = $this->complaint->id;
        $attachment->title = $title;
        $attachment->type = $title;
        $attachment->description = 'Automatically generated ' . strtolower($title);
        $attachment->file_path = '/storage/' . $storagePath;
        $attachment->posted_by = Auth::user()->name;
        $attachment->is_public = 0; 
        $attachment->updated_at = now();
        $attachment->save();

        return $attachment;
    }

    /**
     * Approve CAPA — moves complaint from Stage 3 (Complaint Verification) to Stage 4 (Complaint Pending Closure).
     * Stamps the Lab Manager's approval with user ID and timestamp.
     */
    public function approveCapa()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');

        $current_stage = $this->complaint->complaint_workflow;

        if ($current_stage !== 3) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'CAPA can only be approved from Complaint Verification stage.']);
            return;
        }

        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if (!$resolution) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'No CAPA record found for this complaint. Please complete the CAPA form first.']);
            return;
        }

        // Stamp CAPA approval
        $resolution->capa_approved_by = Auth::id();
        $resolution->capa_approved_at = now();
        $resolution->workflow_stage = 4;
        $resolution->save();

        // Move complaint to Stage 4
        $this->complaint->complaint_workflow = 4;
        $this->complaint->save();

        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = 'CAPA Approved by ' . (Auth::user()?->name ?? 'System') . '. Moved to Complaint Pending Closure.';
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($current_stage);
        $chain->comments = $this->comment;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'CAPA approved. Complaint moved to Pending Closure.']);
        $this->dispatch('complaint-workflow-updated', message: 'CAPA approved. Complaint moved to Complaint Pending Closure.');
    }

    /**
     * Close Complaint — moves from Stage 4 (Complaint Pending Closure) to Stage 5 (Closed Complaint).
     * Optionally emails the closure report to selected contacts before closing.
     */
    public function closeComplaint()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');

        $current_stage = $this->complaint->complaint_workflow;

        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        $sendToCustomer = $resolution?->send_to_customer ?? false;

        // No automated email for system reports - generation only as requested.
        $this->generateClosureReport();

        // Close the complaint
        $this->complaint->complaint_workflow = 5;
        $this->complaint->is_closed = true;
        $this->complaint->closed_by = Auth::id();
        $this->complaint->date_closed = getTodayDate();
        $this->complaint->save();

        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = $sendToCustomer
            ? 'Complaint Closed. Closure report emailed to customer.'
            : 'Complaint Closed.';
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($current_stage);
        $chain->comments = $this->comment;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint has been closed.')
            ->to(\App\Livewire\Crm\Complaint\ComplaintShow::class);
    }

    public function sendReportAndClose()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;
        
        // 1. Retrieve Attachment
        $attachment = \App\Models\CRM\Complaintattachment::where('complaint_id', $this->complaint->id)
            ->where('title', 'Closure Report')
            ->orderBy('id', 'desc')
            ->first();

        if (!$attachment) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Closure Report not found. Please regenerate it.']);
            return;
        }

        // 2. Validate File and Email
        // Remove '/storage/' prefix to check existence on disk 'public'
        $relativePath = str_replace('/storage/', '', $attachment->file_path);
        
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($relativePath)) {
             $this->dispatch('alert', ['type' => 'error', 'message' => 'Report file missing from storage. Please regenerate.']);
             return;
        }

        if (!$this->complaint->client_id) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Complaint has no customer. Cannot send report.']);
            return;
        }

        $contactIds = is_array($this->selectedContactIds) ? $this->selectedContactIds : [];
        if (empty($contactIds)) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Please select at least one contact to receive the closure report.']);
            return;
        }

        $emails = CustomerContact::whereIn('id', $contactIds)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->pluck('email')
            ->unique()
            ->values()
            ->toArray();

        if (empty($emails)) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Selected contacts have no valid email addresses.']);
            return;
        }

        try {
            // Generation only as requested
            $this->generateClosureReport();

            $this->complaint->complaint_workflow = 5; // Closed
            $this->complaint->is_closed = true;
            $this->complaint->save();

            $chain_custody = new Chain_of_Custody_Complaint();
            $chain_custody->complaint_id = $this->complaint->id;
            $chain_custody->action = "Reports Generated & Complaint Closed";
            $chain_custody->action_taker_id = Auth::id();
            $chain_custody->workflow_stage = $this->getStageName($current_stage);
            $chain_custody->comments = $this->comment;
            $chain_custody->move_out_date = getTodayDate();
            $chain_custody->save();

            $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed and reports generated successfully.']);
            $this->dispatch('complaint-workflow-updated', message: 'Complaint closed and reports generated successfully.')
                ->to(\App\Livewire\Crm\Complaint\ComplaintShow::class);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate reports/close complaint: ' . $e->getMessage());
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Failed to close complaint: ' . $e->getMessage()]);
        }
    }

    public function rejectResolution()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Delete');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->rejected = 1;
        $this->complaint->complaint_workflow = 6;
        $this->complaint->reject_workflow = $current_stage;
        $this->complaint->save();

        $new_chain = new Chain_of_Custody_Complaint();
        $new_chain->complaint_id = $this->complaint->id;
        $new_chain->action = "Reject Resolution";
        $new_chain->action_taker_id = Auth::id();
        $new_chain->workflow_stage = $this->getStageName($current_stage); 
        $new_chain->comments = $this->comment;
        $new_chain->move_out_date = getTodayDate();
        $new_chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution rejected.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution rejected.');
    }

    public function reverseResolution()
    {
        $this->checkPermission('CRM.components.Complaint Verification.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;
        $this->complaint->complaint_workflow = 3; // Back to Complaints Resolution
        $this->complaint->save();

        $new_custody = new Chain_of_Custody_Complaint();
        $reverse_actions = getComplaintActionReverse();
        $action = $reverse_actions[$current_stage] ?? 'Return Resolution';

        $new_custody->complaint_id = $this->complaint->id;
        $new_custody->action = $action;
        $new_custody->action_taker_id = Auth::id();
        $new_custody->workflow_stage = $this->getStageName($current_stage); 
        $new_custody->comments = $this->comment;
        $new_custody->move_out_date = getTodayDate();
        $new_custody->save();
        
        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution returned to complaints resolution section.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution returned to the previous stage.');
    }

    #[On('complaint-workflow-updated')]
    public function refreshWorkflow()
    {
        $this->complaint->refresh();
    }

    #[On('sync-closure-contacts')]
    public function syncClosureContacts(array $contactIds)
    {
        // Only the modal handler instance should respond
        if ($this->viewMode !== 'modal') {
            return;
        }
        $this->selectedContactIds = $contactIds;
    }

    #[On('direct-close-complaint')]
    public function directCloseComplaint()
    {
        // Only the modal handler instance should respond
        if ($this->viewMode !== 'modal') {
            return;
        }
        $this->closeComplaint();
    }

    public function render()
    {
        $workflow_values = getComplaintsWorkFlowValues();
        $current_workflow_name = '';
        foreach($workflow_values as $x => $x_value){
            if($x_value == $this->complaint->complaint_workflow){
                $current_workflow_name = $x;
            }
        }

        $closureContacts = $this->getClosureContacts();
        $responsibleOfficers = $this->getResponsibleOfficersByStage();

        return view('livewire.crm.complaint.tabs.complaint-workflow-tab', [
            'currentWorkflowName' => $current_workflow_name,
            'chainOfCustody' => $this->chainOfCustody,
            'closureContacts' => $closureContacts,
            'responsibleOfficersByStage' => $responsibleOfficers,
        ]);
    }

    /**
     * Get customer contacts eligible to receive closure reports (receive_report = 1).
     * Same logic as CustomerContactController::get_contacts('receive_report', ...).
     */
    private function getClosureContacts()
    {
        if (!$this->complaint->client_id) {
            return collect();
        }
        $customerId = $this->complaint->client_id;
        return CustomerContact::where('crm_customer_id', $customerId)
            ->where('receive_report', 1)
            ->where('active', 1)
            ->orderBy('first_name', 'asc')
            ->get();
    }

    // Helper to convert IDs (1) to Names (Open Complaints)
    private function getStageName($id)
    {
        return getComplaintWorkflow()[$id] ?? $id;
    }

    /**
     * Identifies users responsible for each workflow stage based on session-style permissions
     */
    public function getResponsibleOfficersByStage()
    {
        // Permission mappings for each stage
        $stages = [
            1 => ['CRM', 'components', 'Open Complaint', 'Edit'],
            2 => ['CRM', 'components', 'Complaint Investigation', 'Edit'],
            3 => ['CRM', 'components', 'Complaint Verification', 'Edit'],
            4 => ['CRM', 'components', 'Complaint Pending Closure', 'Edit'],
            5 => ['CRM', 'components', 'Complaint Pending Closure', 'Edit'], // Closed complaints managed by pending closure approvers
        ];

        $roles = \App\Role::all();
        $officers = [];

        foreach ($stages as $stageId => $perm) {
            $officers[$stageId] = [];
            foreach ($roles as $role) {
                $perms = json_decode($role->permissions, true);
                // Navigate the nested array structure defined in User.php check_permission
                if (isset($perms[$perm[0]][$perm[1]][$perm[2]][$perm[3]]) && $perms[$perm[0]][$perm[1]][$perm[2]][$perm[3]] == "true") {
                    $roleUsers = $role->getUsersByRole();
                    foreach ($roleUsers as $user) {
                        $officers[$stageId][$user->id] = $user->name;
                    }
                }
            }
        }
        return $officers;
    }
}