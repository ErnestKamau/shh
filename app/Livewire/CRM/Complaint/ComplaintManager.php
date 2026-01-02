<?php

namespace App\Livewire\CRM\Complaint;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CRM\Complaint;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ComplaintManager extends Component
{
    use WithPagination;

    // Search and Filter
    public $search = '';

    public $stageFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Modals
    public $showCreateModal = false;
    public $showActionModal = false; // For Approve/Reject/Reverse
    public $showViewModal = false;

    // Complaint Form
    public $complaintId = null;
    public $complaintForm = [
        'description' => '',
        'priority' => 'Normal',
        'type' => '',
        'received_from' => '', // Customer Name or Custom
        'date' => '',
        'client_id' => null,
    ];

    // Action Form
    public $actionType = ''; // 'approve', 'reject', 'reverse'
    public $actionComment = '';
    public $selectedComplaintForAction = null;

    // View Data
    public $viewComplaint = null;
    public $workflowValues = [];

    // Support Data
    public $customers = [];
    public $complaintTypes = [];

    protected $rules = [
        'complaintForm.description' => 'required|string',
        'complaintForm.priority' => 'required|string',
        'complaintForm.type' => 'required|string',
        'complaintForm.date' => 'required|date',
        'complaintForm.received_from' => 'required|string',
    ];

    public function mount($stage = null)
    {
        $this->workflowValues = getComplaintsWorkFlowValues();
        $this->complaintForm['date'] = date('Y-m-d');
        
        // Handle stage parameter from URL (passed from controller)
        if ($stage) {
            // Convert stage name to value
            if (isset($this->workflowValues[$stage])) {
                $this->stageFilter = $this->workflowValues[$stage];
            } else {
                // If it's already a numeric value, use it directly
                $this->stageFilter = is_numeric($stage) ? (int)$stage : '';
            }
        } elseif ($this->stageFilter && !is_numeric($this->stageFilter)) {
            // Convert stage name to value if passed as query param
             $this->stageFilter = $this->workflowValues[$this->stageFilter] ?? '';
        }
    }

    public function loadSupportData()
    {
        $this->customers = CRMCustomer::orderBy('name')->get();
        $this->complaintTypes = Complaint_Type::all();
    }

    public function render()
    {
        $query = Complaint::with(['customer']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('complaint_id', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('received_from', 'like', '%' . $this->search . '%');
            });
        }

        // Handle stage filtering
        // stageFilter = '' or null means "All Complaints" - show everything (except rejected)
        // stageFilter = 0 means "All Complaints" - show everything (except rejected)
        // stageFilter = 6 means "Cancelled Complaints" - show rejected complaints
        // Other values (1-5) filter by complaint_workflow
        if ($this->stageFilter !== '' && $this->stageFilter !== null && $this->stageFilter !== '0') {
            if ($this->stageFilter == 6) {
                // Cancelled Complaints - show rejected complaints
                $query->where('rejected', 1);
            } elseif ($this->stageFilter > 0) {
                // Filter by workflow stage (exclude rejected ones)
                $query->where('complaint_workflow', $this->stageFilter)
                      ->where('rejected', 0);
            }
        } else {
            // "All Complaints" - show all non-rejected complaints
            $query->where('rejected', 0);
        }

        if ($this->dateFrom) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        $complaints = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.crm.complaint.complaint-manager', [
            'complaints' => $complaints
        ]);
    }

    // Modal Triggers
    public function openCreateModal($id = null)
    {
        $this->resetValidation();
        $this->loadSupportData();
        
        if ($id) {
            $complaint = Complaint::find($id);
            $this->complaintId = $id;
            $this->complaintForm = [
                'description' => $complaint->description,
                'priority' => $complaint->priority,
                'type' => $complaint->type,
                'received_from' => $complaint->received_from,
                'date' => $complaint->date,
                'client_id' => $complaint->client_id,
            ];
        } else {
            $this->complaintId = null;
            $this->complaintForm = [
                'description' => '',
                'priority' => 'Normal',
                'type' => '',
                'received_from' => '',
                'date' => date('Y-m-d'),
                'client_id' => null,
            ];
        }
        
        $this->showCreateModal = true;
    }

    public function openActionModal($id, $type)
    {
        $this->selectedComplaintForAction = Complaint::find($id);
        $this->actionType = $type; // approve, reject, reverse
        $this->actionComment = '';
        $this->showActionModal = true;
    }

    public function openViewModal($id)
    {
        $this->viewComplaint = Complaint::with(['customer', 'chainOfCustody.user'])->find($id);
        $this->showViewModal = true;
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showActionModal = false;
        $this->showViewModal = false;
    }

    // CRUD
    public function saveComplaint()
    {
        $this->validate();

        if ($this->complaintId) {
            $complaint = Complaint::find($this->complaintId);
            $action = "Edit complaint";
        } else {
            $complaint = new Complaint();
            
            // Generate ID
            $count = Complaint::count() + 1;
            $complaint->complaint_id = "COMP" . str_pad($count, 4, '0', STR_PAD_LEFT);
            $complaint->registered_by = Auth::user()->name;
            $complaint->complaint_workflow = 1; 
            $action = "Create complaint";
        }

        $complaint->description = $this->complaintForm['description'];
        $complaint->priority = $this->complaintForm['priority'];
        $complaint->type = $this->complaintForm['type'];
        $complaint->received_from = $this->complaintForm['received_from'];
        $complaint->date = $this->complaintForm['date'];

        // Link customer if name matches
        $customer = CRMCustomer::where('name', $this->complaintForm['received_from'])->first();
        if ($customer) {
            $complaint->client_id = $customer->id;
        }

        $complaint->save();

        // Chain of Custody
        $this->logChainOfCustody($complaint, $action, $complaint->complaint_workflow);

        // Notify if creating
        if (!$this->complaintId) {
            $this->notifyPersonnel($complaint);
        }

        $this->closeModal();
        session()->flash('success', $this->complaintId ? 'Complaint updated successfully' : 'Complaint created successfully');
    }

    // Workflow Actions
    public function performAction()
    {
        $complaint = $this->selectedComplaintForAction;
        $stages = getComplaintsWorkFlowValues();
        $currentStage = $complaint->complaint_workflow;
        
        // Find current stage name
        $currentStageName = array_search($currentStage, $stages);

        if ($this->actionType === 'approve') {
             // Find next stage - increment workflow value by 1
             $nextStageValue = $currentStage + 1;
             
             // Don't allow going beyond stage 5 (Closed Complaints)
             if ($nextStageValue > 5) {
                 session()->flash('error', 'Complaint is already at the final stage');
                 return;
             }
                 
                 $complaint->complaint_workflow = $nextStageValue;
             
             // Get action name from helper
             $approvalActions = getComplaintsActionsApproval();
             $action = $approvalActions[$currentStage] ?? 'Approved';
                 
             if ($nextStageValue == 5) { // Closed Complaints
                     $complaint->edited_by = Auth::user()->name;
             }

        } elseif ($this->actionType === 'reverse') {
            // Find previous stage - decrement workflow value by 1
            $prevStageValue = $currentStage - 1;
            
            // Don't allow going below stage 1 (Open Complaints)
            if ($prevStageValue < 1) {
                session()->flash('error', 'Cannot reverse beyond initial stage');
                 return; 
             }
            
            $complaint->complaint_workflow = $prevStageValue;
            
            // Get reverse action name from helper
            $reverseActions = getComplaintActionReverse();
            $action = $reverseActions[$currentStage] ?? 'Reversed';

        } elseif ($this->actionType === 'reject') {
            $complaint->rejected = 1;
            $complaint->complaint_workflow = 6; // Cancelled Complaints
            $complaint->reject_workflow = $currentStage;
            $action = "Reject Complaint";
        }

        $complaint->save();
        $this->logChainOfCustody($complaint, $action, $currentStage, $this->actionComment);

        $this->closeModal();
        session()->flash('success', 'Action completed successfully');
    }

    private function logChainOfCustody($complaint, $action, $workflowStage, $comments = null)
    {
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $complaint->id;
        $chain->action = $action;
        $chain->action_taker_id = Auth::id();
        $chain->workflow_stage = $workflowStage;
        $chain->comments = $comments;
        $chain->move_out_date = getTodayDate();
        $chain->save();
    }

    private function notifyPersonnel($complaint)
    {
        $config = SystemConfigurationsType::where('configuration_type','Personnel to Recieve Feedback and Complaint Notification')->first();
        if(isset($config->id)){
            $config_users = SystemConfiguration::where('configuration_type_id',$config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Complaint Notification - '.$complaint->complaint_id;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a complaint of ID <b>'.$complaint->complaint_id.'</b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                // Assuming notify_user is a global helper
                notify_user($body,$user->value,$subject);
            }
        }
    }
}
