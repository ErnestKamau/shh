<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Models\CRM\CustomerFeedback;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithPagination;

class CustomerFeedbacksTab extends BaseCrmComponent
{
    use WithPagination;

    public $customer;
    // public $feedbacks; // Removed as we pass it to view directly
    public $search = '';
    public $showForm = false;
    public $editingFeedbackId = null;
    public $selectedFeedback = null;
    public $perPage = 10;
    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'feedback-saved' => 'refreshFeedbacks',
        'feedback-form-closed' => 'closeForm'
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

    public function refreshFeedbacks()
    {
        $this->showForm = false;
        $this->editingFeedbackId = null;
        $this->resetPage();
    }

    public function openAddForm()
    {
        $this->editingFeedbackId = null;
        $this->showForm = true;
    }

    public function editFeedback($id)
    {
        $this->editingFeedbackId = $id;
        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->editingFeedbackId = null;
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'feedbacks', $this->search))
            ->download('customer_feedbacks_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function viewFeedback($id)
    {
        $this->selectedFeedback = CustomerFeedback::with(['ratings.metric', 'customer', 'contact'])->find($id);
        $this->dispatch('open-view-modal');
    }

    public function render()
    {
        $query = CustomerFeedback::where('customer_id', $this->customer->id);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('feedback', 'like', '%' . $this->search . '%')
                    ->orWhere('received_from', 'like', '%' . $this->search . '%');
            });
        }

        $feedbacks = $query->with(['contact'])->orderBy('id', 'desc')->paginate($this->perPage);

        return view('livewire.crm.customer.tabs.customer-feedbacks-tab', [
            'feedbacks' => $feedbacks,
        ]);
    }
}
