<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaint;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\CustomerContact;
use App\Services\CRM\ComplaintInvestigationReportService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
    public $ncr_required = 0; // 0 = No, 1 = Yes
    public $viewMode = 'tab'; // 'tab' or 'modal'

    // Closure Properties
    public $send_to_customer = false;
    public $internal_remarks = '';
    public $client_remarks = '';
    public $complaint_review_remarks = '';
    
    // Modal Properties
    public $modalAction = '';
    public $modalTitle = '';
    public $confirmButtonText = '';
    public $confirmButtonColor = 'btn-primary';
    
    // Workflow State
    public $workflowState = '';
    public $progressText = '';
    
    // Contacts
    public $selectedContactIds = [];
    
    /**
     * Property to check if interim approval has been recorded in chain of custody.
     */
    public function getIsApprovedForClosureProperty(): bool
    {
        return $this->complaint->chainOfCustody()
            ->where('action', 'like', '%Approval Recorded%')
            ->exists();
    }

    public function getChainOfCustodyProperty()
    {
        return Chain_of_Custody_Complaint::with(['complaint', 'actionTaker'])
            ->where('complaint_id', $this->complaintId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Property to check if closure remarks have been added.
     */
    public function getHasClosureRemarksProperty(): bool
    {
        $res = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        return !empty(trim(strip_tags((string)($res->internal_remarks ?? ''))));
    }

    /**
     * Combined property to determine if complaint can move to final closure.
     */
    public function getCanFinallyCloseProperty(): bool
    {
        return $this->getIsApprovedForClosureProperty() && $this->getHasClosureRemarksProperty();
    }

    public function mount($complaintId)
    {
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::find($complaintId);
        $this->selectedContactIds = [];
        $this->workflowStages = getComplaintWorkflowStages();
        
        // Load existing remarks if any
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if ($resolution) {
            $this->client_remarks = $resolution->client_remarks ?? '';
            $this->complaint_review_remarks = $resolution->internal_remarks ?? '';
        }
        
        $this->dispatch('content-updated');
    }

    public function render()
    {
        $workflow_values = getComplaintWorkflow();
        $current_workflow_name = '';
        foreach($workflow_values as $x => $x_value){
            if($x_value == $this->complaint->complaint_workflow){
                $current_workflow_name = $x;
            }
        }

        $closureContacts = $this->getClosureContacts();
        $responsibleOfficers = $this->getResponsibleOfficersByStage();

        return view('livewire.crm.complaint.tabs.complaint-workflow-tab', [
            'complaint' => $this->complaint,
            'workflowStages' => $this->workflowStages,
            'perPage' => $this->perPage,
            'comment' => $this->comment,
            'car_required' => $this->car_required,
            'ncr_required' => $this->ncr_required,
            'viewMode' => $this->viewMode,
            'send_to_customer' => $this->send_to_customer,
            'internal_remarks' => $this->internal_remarks,
            'client_remarks' => $this->client_remarks,
            'complaint_review_remarks' => $this->complaint_review_remarks,
            'currentWorkflowName' => $current_workflow_name,
            'chainOfCustody' => $this->chainOfCustody,
            'closureContacts' => $closureContacts,
            'responsibleOfficersByStage' => $responsibleOfficers,
        ]);
    }

    #[On('initiate-workflow-action')]
    public function initiateWorkflowAction($action)
    {
        // Normalize payload: some callers dispatch an object like { action: 'name' }
        if (is_array($action) && isset($action['action'])) {
            $action = $action['action'];
        } elseif (is_object($action) && property_exists($action, 'action')) {
            $action = $action->action;
        }

        $this->modalAction = $action;
        $this->resetValidation();
        $this->setModalContent($action);
        $this->dispatch('show-action-modal');
    }

    public function resetValidation($field = null)
    {
        $this->resetErrorBag();
        $this->comment = '';
        $this->send_to_customer = false;
        $this->selectedContactIds = [];
        $this->internal_remarks = '';
        $this->client_remarks = '';
        $this->complaint_review_remarks = '';
    }

    private function setModalContent($action)
    {
        $this->modalTitle = '';
        $this->confirmButtonText = '';
        $this->confirmButtonColor = 'btn-primary';

        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();

        switch ($action) {
            case 'approveNext':
                if ($this->complaint->complaint_workflow == 4) {
                    $this->modalTitle = 'Approve Resolution & Move to Next Stage';
                    $this->confirmButtonText = 'Approve Resolution';
                } elseif ($this->complaint->complaint_workflow == 2) {
                    $this->modalTitle = 'Approve Complaint Resolution & Move to Resolution Approval Stage';
                    $this->confirmButtonText = 'Approve & Advance';
                } else {
                    $this->modalTitle = 'Approve Investigation & Move to Resolution';
                    $this->confirmButtonText = 'Approve Investigation';
                }
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'approveCapa':
                $this->modalTitle = 'Approve Complaint Resolution & Move to Resolution Approval Stage';
                $this->confirmButtonText = 'Approve & Advance';
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'requestResolutionApprove':
                $this->modalTitle = 'Request Resolution Approval';
                $this->confirmButtonText = 'Request Approval';
                $this->confirmButtonColor = 'btn-secondary';
                break;
            case 'approveAndNotify':
                $this->modalTitle = 'Approve Resolution & Send Report';
                $this->confirmButtonText = 'Approve & Send';
                $this->confirmButtonColor = 'btn-success';
                break;
            case 'investigationCheckpoint':
                $this->modalTitle = 'Investigation Checkpoint';
                $this->confirmButtonText = 'Save Checkpoint';
                $this->confirmButtonColor = 'btn-outline-primary';
                break;
            case 'saveClosureRemarks':
                $this->modalTitle = 'Add Closure Remarks';
                $this->confirmButtonText = 'Save Remarks';
                $this->confirmButtonColor = 'btn-outline-primary';
                break;
            case 'finalCloseComplaintAction':
                $this->modalTitle = 'Close Complaint';
                $this->confirmButtonText = 'Close Complaint';
                $this->confirmButtonColor = 'btn-danger';
                break;
            case 'returnToResolution':
            case 'reverseApproval':
            case 'reverseResolution':
                $this->modalTitle = 'Return Complaint';
                $this->confirmButtonText = 'Return Complaint';
                $this->confirmButtonColor = 'btn-outline-warning';
                break;
            case 'reject':
                $this->modalTitle = 'Cancel Complaint';
                $this->confirmButtonText = 'Cancel Complaint';
                $this->confirmButtonColor = 'btn-danger';
                break;
        }
    }

    public function performAction()
    {
        $this->workflowState = 'processing';
        
        try {
            switch ($this->modalAction) {
                case 'approveNext':
                    $this->approveNext();
                    break;
                case 'approveCapa':
                    $this->approveCapa();
                    break;
                case 'requestResolutionApprove':
                    $this->requestResolutionApprove();
                    break;
                case 'approveAndNotify':
                    $this->approveAndNotify();
                    break;
                case 'investigationCheckpoint':
                    $this->investigationCheckpoint();
                    break;
                case 'saveClosureRemarks':
                    $this->saveClosureRemarks();
                    break;
                case 'finalCloseComplaintAction':
                    $this->finalCloseComplaint();
                    break;
                case 'returnToResolution':
                case 'reverseApproval':
                case 'reverseResolution':
                    $this->returnToResolution();
                    break;
                case 'reject':
                    $this->reject();
                    break;
            }
            
            $this->workflowState = 'success';
            $this->dispatch('close-action-modal');
        } catch (ValidationException $e) {
            $this->workflowState = 'error';
            $this->setErrorBag($e->validator->getMessageBag());
            $message = collect($e->errors())->flatten()->first() ?? 'Please correct the highlighted fields.';
            $this->dispatch('alert', ['type' => 'error', 'message' => $message]);
        } catch (\Exception $e) {
            $this->workflowState = 'error';
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function approveNext()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $current_stage = $this->complaint->complaint_workflow;
        $stageName = $this->getStageName($current_stage);


        
        if ($current_stage == 1) {
            // Move from Stage 1 (Open Complaint) to Stage 2 (Complaint Resolution)
            $this->complaint->complaint_workflow = 2;
            $this->complaint->save();
            
            $action = match($current_stage) {
                1 => 'Approve & Move to Resolution Stage',
                default => 'Workflow Advanced'
            };
            
        } elseif ($current_stage == 2) {
            // New logic: Support optional report notification when moving to Stage 4
            $this->validate([
                'send_to_customer' => 'boolean',
                'selectedContactIds' => 'nullable|array',
                'selectedContactIds.*' => 'string',
            ]);

            if ($this->send_to_customer) {
                $emails = $this->sendInvestigationReportToSelectedContacts();
                $this->recordReportSendAudit(
                    'Investigation report emailed during Resolution approval.',
                    $stageName,
                    $emails
                );
            }

            // Move from Stage 2 (Complaint Resolution) to Stage 4 (Resolution Approval)
            $this->complaint->complaint_workflow = 4;
            $this->complaint->save();
            
            $action = 'Resolution Approved and Advanced to Resolution Approval';
            if ($this->send_to_customer) $action .= ' (Report Sent)';
            
        } else {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Invalid workflow stage for approval.']);
            return;
        }

        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $this->complaint->id;
        $chain_custody->action = $action;
        $chain_custody->action_taker_id = Auth::id();
        $chain_custody->workflow_stage = $stageName;
        $chain_custody->move_out_date = getTodayDate();
        $chain_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => $action]);
        $this->dispatch('complaint-workflow-updated', message: $action);
    }

    public function approveCapa()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');

        $this->validate([
            'send_to_customer' => 'boolean',
            'selectedContactIds' => 'nullable|array',
            'selectedContactIds.*' => 'string',
        ]);
        
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        
        if (!$resolution) {
            $resolution = new \App\Models\CRM\Complaintsresolutions();
            $resolution->complaint_id = $this->complaint->id;
            $resolution->save();
        }

        $current_stage = $this->complaint->complaint_workflow;
        $stageName = $this->getStageName($current_stage);

        if ($this->send_to_customer) {
            $emails = $this->sendInvestigationReportToSelectedContacts();
            $this->recordReportSendAudit(
                'Investigation report emailed during CAPA approval.',
                $stageName,
                $emails
            );
        }

        // Update the resolution with the Lab Manager's approval
        $resolution->approve = 1;
        $resolution->capa_approved_by = Auth::id();
        $resolution->capa_approved_at = now();
        $resolution->save();

        // Move complaint from Stage 2 (Complaint Resolution) to Stage 4 (Resolution Approval)
        $this->complaint->complaint_workflow = 4;
        $this->complaint->save();

        $actionBase = (!$resolution->car_required) ? 'Resolution Approval' : 'CAPA Approval';
        $action = $actionBase . ' by ' . (Auth::user()?->name ?? 'System') . '. Moved to Resolution Approval.';
        if ($this->send_to_customer) $action .= ' Report sent to client.';

        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = $action;
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $stageName;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution approved and advanced successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution approved and advanced successfully.');
    }

    public function requestResolutionApprove()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $this->complaint->id;
        $chain_custody->action = "Request Resolution Approval";
        $chain_custody->action_taker_id = Auth::id();
        // Updated: Use the helper to save text instead of number
        $chain_custody->workflow_stage = $this->getStageName($current_stage);
        $chain_custody->move_out_date = getTodayDate();
        $chain_custody->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Resolution approval requested successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Resolution approval requested successfully.');
    }

    public function approveAndNotify()
    {
        $this->checkPermission('CRM.components.Resolution Approval.Edit');
        
        $this->validate([
            'send_to_customer' => 'boolean',
            'selectedContactIds' => 'nullable|array',
            'selectedContactIds.*' => 'string',
            'internal_remarks' => 'nullable|string',
            'client_remarks' => 'nullable|string',
        ]);

        if ($this->send_to_customer) {
            $emails = $this->sendInvestigationReportToSelectedContacts();
            $this->recordReportSendAudit(
                'Investigation report emailed during resolution approval.',
                $this->getStageName($this->complaint->complaint_workflow),
                $emails
            );
        }

        // Update the resolution with the approval
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if (!$resolution) {
            $resolution = new \App\Models\CRM\Complaintsresolutions();
            $resolution->complaint_id = $this->complaint->id;
            $resolution->save();
        }

        $resolution->approve = 1;
        $resolution->approved_by = Auth::id();
        $resolution->internal_remarks = $this->internal_remarks;
        $resolution->client_remarks = $this->client_remarks;
        $resolution->save();

        // Create chain of custody record
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = 'Resolution Approved by ' . (Auth::user()?->name ?? 'System') . '. Report sent to client: ' . ($this->send_to_customer ? 'Yes' : 'No');
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($this->complaint->complaint_workflow);
        $chain->comments = $this->internal_remarks;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $message = $this->send_to_customer
            ? 'Resolution approved and report sent successfully.'
            : 'Resolution approved successfully.';

        $this->dispatch('alert', ['type' => 'success', 'message' => $message]);
        $this->dispatch('complaint-workflow-updated', message: $message);
    }

    public function investigationCheckpoint()
    {
        $this->checkPermission('CRM.components.Complaint Investigation.Edit');
        
        $this->validate([
            'car_required' => 'required|boolean',
            'ncr_required' => 'required|boolean',
            'comment' => 'nullable|string',
        ]);

        // Update resolution with checkpoint data
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if (!$resolution) {
            $resolution = new \App\Models\CRM\Complaintsresolutions();
            $resolution->complaint_id = $this->complaint->id;
            $resolution->registered_by = Auth::user()?->name;
            $resolution->save();
        }

        $resolution->car_required = $this->car_required;
        $resolution->ncr_required = $this->ncr_required;
        $resolution->save();

        // Create chain of custody record
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $decisionText = $this->car_required ? ($this->ncr_required ? "CAR & NC ASSIGNED" : "CAR ASSIGNED") : "NO CAR ASSIGNED";
        $chain->action = "Classification Determined: " . $decisionText . " & Submitted to Verification";
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($this->complaint->complaint_workflow);
        $chain->comments = $this->comment ?? '';
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Investigation checkpoint updated successfully.']);
        $this->dispatch('checkpoint-completed');
        $this->dispatch('complaint-workflow-updated', message: 'Investigation checkpoint updated successfully.');
    }

    public function saveClosureRemarks()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $this->validate([
            'internal_remarks' => 'required|string',
            'client_remarks' => 'nullable|string',
        ]);

        // Update the resolution with closure remarks
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if ($resolution) {
            $resolution->internal_remarks = $this->internal_remarks;
            $resolution->client_remarks = $this->client_remarks;
            $resolution->save();
        }

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Closure remarks saved successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Closure remarks updated.');
    }

    public function finalCloseComplaint()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $this->validate([
            'comment' => 'nullable|string',
        ]);

        $this->closeComplaintAndGenerateArtifacts(
            'Complaint Closed by ' . (Auth::user()?->name ?? 'System') . '. Comment: ' . strip_tags($this->comment ?? 'N/A'),
            $this->comment ?? ''
        );

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint closed successfully.')
            ->to(\App\Livewire\Crm\Complaint\ComplaintShow::class);
    }

    public function returnToResolution()
    {
        $this->checkPermission('CRM.components.Complaint Pending Closure.Edit');
        
        $this->validate([
            'comment' => 'required|string',
        ]);

        // Move complaint back to Resolution stage (Stage 2)
        $this->complaint->complaint_workflow = 2;
        $this->complaint->save();

        // Create chain of custody record
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = 'Returned to Resolution by ' . (Auth::user()?->name ?? 'System') . '. Reason: ' . strip_tags($this->comment);
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName($this->complaint->complaint_workflow);
        $chain->comments = $this->comment;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint returned to Resolution successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint returned to Resolution successfully.');
    }

    public function reject()
    {
        $this->checkPermission('CRM.components.Open Complaint.Edit');

        $this->validate([
            'comment' => 'required|string',
        ], [
            'comment.required' => 'Please provide a reason for cancellation.'
        ]);

        $this->complaint->complaint_workflow = 6; // Cancelled
        $this->complaint->rejected = true;
        // Optional: you might want to also mark as closed depending on how reporting treats cancelled
        // $this->complaint->is_closed = true; 
        $this->complaint->save();

        // Create chain of custody record
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = 'Complaint Cancelled by ' . (Auth::user()?->name ?? 'System');
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $this->getStageName(6);
        $chain->comments = $this->comment;
        $chain->move_out_date = getTodayDate();
        $chain->save();

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint has been cancelled successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint Cancelled.');
    }

    /**
     * Save client remarks for resolution approval stage.
     */
    public function saveClientRemarks()
    {
        $this->checkPermission('CRM.components.Resolution Approval.Edit');
        
        $this->validate([
            'client_remarks' => 'nullable|string',
        ]);

        // Save to resolutions table
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if ($resolution) {
            $resolution->client_remarks = $this->client_remarks;
            $resolution->save();
        }

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Client remarks saved successfully.']);
    }

    /**
     * Save complaint review remarks for resolution approval stage.
     */
    public function saveComplaintReviewRemarks()
    {
        $this->checkPermission('CRM.components.Resolution Approval.Edit');
        
        $this->validate([
            'complaint_review_remarks' => 'required|string',
        ]);

        // Save to resolutions table
        $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
        if ($resolution) {
            $resolution->internal_remarks = $this->complaint_review_remarks;
            $resolution->save();
        }

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint review remarks saved successfully.']);
    }

    /**
     * Save both remarks and close complaint in single action.
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

        // Ensure complaint is in resolution approval stage
        if ($this->complaint->complaint_workflow != 4) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Complaint must be in Resolution Approval stage to close.']);
            return;
        }

        try {
            $this->closeComplaintAndGenerateArtifacts(
                'Complaint Closed by ' . (Auth::user()?->name ?? 'System') . '. Final remarks recorded.',
                $this->complaint_review_remarks,
                function (): void {
                    $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
                    if ($resolution) {
                        $resolution->client_remarks = $this->client_remarks;
                        $resolution->internal_remarks = $this->complaint_review_remarks;
                        $resolution->save();
                    }
                },
                4
            );
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed successfully with remarks.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint closed successfully.');
    }

    /**
     * Close complaint with remarks after approval.
     */
    public function closeComplaintWithRemarks()
    {
        $this->checkPermission('CRM.components.Resolution Approval.Edit');
        
        $this->validate([
            'complaint_review_remarks' => 'required|string',
        ]);

        // Ensure complaint is in resolution approval stage
        if ($this->complaint->complaint_workflow != 4) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Complaint must be in Resolution Approval stage to close.']);
            return;
        }

        try {
            $this->closeComplaintAndGenerateArtifacts(
                'Complaint Closed by ' . (Auth::user()?->name ?? 'System') . '. Final remarks recorded.',
                $this->complaint_review_remarks,
                function (): void {
                    $resolution = \App\Models\CRM\Complaintsresolutions::where('complaint_id', $this->complaint->id)->first();
                    if ($resolution) {
                        $resolution->internal_remarks = $this->complaint_review_remarks;
                        $resolution->client_remarks = $this->client_remarks;
                        $resolution->save();
                    }
                },
                4
            );
        } catch (\Throwable $e) {
            $this->dispatch('alert', ['type' => 'error', 'message' => $e->getMessage()]);
            return;
        }

        $this->dispatch('alert', ['type' => 'success', 'message' => 'Complaint closed successfully.']);
        $this->dispatch('complaint-workflow-updated', message: 'Complaint closed successfully.');
    }

    /**
     * Get closure contacts for sending reports.
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

    /**
     * @return array<int, string>
     */
    private function sendInvestigationReportToSelectedContacts(): array
    {
        $this->validate([
            'selectedContactIds' => 'required|array|min:1',
            'selectedContactIds.*' => 'string',
        ], [
            'selectedContactIds.required' => 'Select at least one contact to receive the report.',
            'selectedContactIds.min' => 'Select at least one contact to receive the report.',
        ]);

        $emails = app(ComplaintInvestigationReportService::class)
            ->sendToSelectedContacts($this->complaint, $this->selectedContactIds);

        $this->dispatch('attachment-added');

        return $emails;
    }

    /**
     * @param  array<int, string>  $emails
     */
    private function recordReportSendAudit(string $action, string $workflowStage, array $emails): void
    {
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $this->complaint->id;
        $chain->action = $action;
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $workflowStage;
        $chain->comments = 'Sent to: ' . implode(', ', $emails);
        $chain->move_out_date = getTodayDate();
        $chain->save();
    }

    private function closeComplaintAndGenerateArtifacts(
        string $action,
        ?string $comments = null,
        ?callable $beforeClose = null,
        ?int $workflowStageId = null
    ): void {
        $currentStage = $workflowStageId ?? (int) $this->complaint->complaint_workflow;

        DB::transaction(function () use ($action, $comments, $beforeClose, $currentStage): void {
            if ($beforeClose) {
                $beforeClose();
            }

            $this->complaint->complaint_workflow = 5;
            $this->complaint->is_closed = true;
            $this->complaint->closed_by = Auth::id();
            $this->complaint->date_closed = now();
            $this->complaint->save();

            // Update feedback status if linked
            if ($this->complaint->feedback_id) {
                $feedback = $this->complaint->feedback;
                if ($feedback) {
                    $feedback->update(['status' => \App\Models\CRM\CustomerFeedback::STATUS_RESOLVED_VIA_CAPA]);
                }
            }

            $chain = new Chain_of_Custody_Complaint();
            $chain->complaint_id = $this->complaint->id;
            $chain->action = $action;
            $chain->action_taker_id = Auth::id();
            $chain->workflow_stage = $this->getStageName($currentStage);
            $chain->comments = $comments;
            $chain->move_out_date = getTodayDate();
            $chain->save();

            $this->complaint->refresh();

            app(ComplaintInvestigationReportService::class)
                ->generateAndAttachCloseReports($this->complaint);
        });

        $this->dispatch('attachment-added');
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
            2 => ['CRM', 'components', 'Complaint Resolution', 'Edit'],
            3 => ['CRM', 'components', 'Complaint Verification', 'Edit'],
            4 => ['CRM', 'components', 'Resolution Approval', 'Edit'],
        ];

        $roles = \App\Models\Auth\Role::all();
        $officers = [];

        foreach ($stages as $stageId => $perm) {
            $officers[$stageId] = [];
            foreach ($roles as $role) {
                $perms = json_decode($role->permissions, true);
                // Navigate to nested array structure defined in User.php check_permission
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
