<?php

namespace App\Livewire\Crm\Complaint;

use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Livewire\Crm\BaseCrmComponent;
use App\Constants\CRM\CrmConstants;

class ComplaintList extends BaseCrmComponent
{
    public $selectedComplaint = null;
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $priorityFilter = '';
    public $perPage = 10;
    public $stage;
    public $complaintTypes;
    public $showForm = false;
    public $editingComplaintId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'activeTab' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    protected $listeners = [
        'complaint-saved' => 'refreshList',
        'complaint-form-closed' => 'closeComplaintModal'
    ];

    public function mount($stage = null)
    {
        $this->initialize();
        $this->checkPermission(CrmConstants::PERMISSION_COMPLAINT_VIEW);
        $this->stage = $stage;
        $this->complaintTypes = Complaint_Type::orderBy('name')->get();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }

   public function getBreadcrumbItemsProperty()
    {
        return [
            [
                'link' => route('customers-list'), // Ensure this route exists
                'name' => 'CRM',
                'icon' => null
            ],
            [
                'link' => '#',
                // FIX: Use the helper to get the name
                'name' => $this->getStageName($this->stage),
                'icon' => null
            ]
        ];
    }

    public function refreshList()
    {
        $this->showForm = false;
        $this->editingComplaintId = null;
        $this->dispatch('close-complaint-modal');
    }

    public function closeComplaintModal()
    {
        $this->showForm = false;
        $this->editingComplaintId = null;
        $this->dispatch('close-complaint-modal');
    }

    public function addComplaint()
    {
        $this->showForm = true;
        $this->dispatch('add-complaint');
        $this->dispatch('show-complaint-modal');
    }

    public function editComplaint($id)
    {
        $this->editingComplaintId = $id;
        $this->showForm = true;
        $this->dispatch('edit-complaint', complaintId: $id);
        $this->dispatch('show-complaint-modal');
    }

    public function viewDescription($id)
    {
        $this->selectedComplaint = Complaint::find($id);
        $this->dispatch('show-description-modal');
    }

    public $activeTab = 'all';

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        // Use the global helper to get the Name -> Number mapping
        $stage_map = getComplaintsWorkFlowValues();

        $stage_value = \App\Constants\CRM\CrmConstants::WORKFLOW_STAGE_ALL;

        // 2. Determine the Stage ID
        if (is_numeric($this->stage)) {
            $stage_value = $this->stage;
        } elseif (is_string($this->stage)) {
            $decoded_name = urldecode($this->stage);
            if (isset($stage_map[$decoded_name])) {
                $stage_value = $stage_map[$decoded_name];
            }
        }

        // 3. Start the Query
        $query = Complaint::query();

        // 4. Apply the Filter
        if ($stage_value > 0) {
            $query->where('complaint_workflow', $stage_value);
        } else {
            // Logic for "All Complaints" (Stage 0 or Unknown)
            if ($this->activeTab !== 'all' && is_numeric($this->activeTab)) {
                $query->where('complaint_workflow', $this->activeTab);
            }
        }

        // 5. Apply Search & Other Filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('complaint_id', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('received_from', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->priorityFilter) {
            $query->where('priority', $this->priorityFilter);
        }

        return $query;
    }

    public function getComplaintsProperty()
    {
        return $this->getBaseQuery()->with(['client', 'closedBy'])->orderBy('id', 'desc')->paginate($this->perPage);
    }

    public function getHighPriorityCountProperty()
    {
        return $this->getBaseQuery()->where('priority', 'high')->count();
    }


    private function getStageName($value)
    {
        // Use the global helper to get the ID -> Name mapping
        $id_to_name = getComplaintWorkflow();

        // 2. Handle Numeric Input (e.g., URL is .../4)
        if (is_numeric($value)) {
            $stageName = $id_to_name[$value] ?? 'All Complaints';
            return translateComplaintWorkflowStage($stageName);
        }

        // 3. Handle String Input (e.g., URL is .../Resolution%20Approval)
        if (is_string($value)) {
            $decoded_name = urldecode($value);
            
            // If the text matches one of our known stages, return it directly
            if (in_array($decoded_name, $id_to_name)) {
                return translateComplaintWorkflowStage($decoded_name);
            }
        }

        return translateComplaintWorkflowStage('All Complaints');
    }
    public function exportToExcel()
    {
        $this->checkPermission(\App\Constants\CRM\CrmConstants::PERMISSION_COMPLAINT_VIEW);

        $filters = [
            'stage' => $this->stage,
            'search' => $this->search,
            'statusFilter' => $this->statusFilter,
             // The query inside getComplaintsProperty uses 'activeTab' for status in all-complaints mode
            'activeTab' => $this->activeTab,
            'typeFilter' => $this->typeFilter,
            'priorityFilter' => $this->priorityFilter,
        ];

        return (new \App\Exports\CRM\ComplaintsExport($filters))->download('crm_complaints_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.crm.complaint.complaint-list', [
            'complaints' => $this->complaints,
            'complaintTypes' => $this->complaintTypes,
            // Pass the title to the view so you can use {{ $pageTitle }} in the H1 header
            'pageTitle' => $this->getStageName($this->stage) 
        ])->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => true])
            ->section('content2');
    }
}
