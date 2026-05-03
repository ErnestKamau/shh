<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Models\CRM\Complaint;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithPagination;

class CustomerComplaintsTab extends BaseCrmComponent
{
    use WithPagination;

    public $customer;
    // public $complaints; // Removed as we pass it to view directly
    public $search = '';
    public $showForm = false;
    public $editingComplaintId = null;
    public $selectedComplaint = null;
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'complaint-saved' => 'refreshComplaints',
        'complaint-form-closed' => 'closeForm'
    ];

    public function mount($customer)
    {
        $this->customer = $customer;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function refreshComplaints()
    {
        $this->showForm = false;
        $this->editingComplaintId = null;
        $this->resetPage();
    }

    public function openAddForm()
    {
        $this->editingComplaintId = null;
        $this->showForm = true;
    }

    public function editComplaint($id)
    {
        $this->editingComplaintId = $id;
        $this->showForm = true;
    }

    public function viewComplaint($id)
    {
        $this->selectedComplaint = Complaint::find($id);
        $this->dispatch('open-view-modal');
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->editingComplaintId = null;
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'complaints', $this->search))
            ->download('customer_complaints_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        $query = Complaint::where('client_id', $this->customer->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('complaint_id', 'like', '%' . $this->search . '%')
                  ->orWhere('received_from', 'like', '%' . $this->search . '%');
            });
        }

        $complaints = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.crm.customer.tabs.customer-complaints-tab', [
            'complaints' => $complaints,
        ]);
    }
}
