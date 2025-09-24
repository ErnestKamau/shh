<?php

namespace App\Livewire\Amendments;

use Livewire\Component;
use Livewire\WithPagination;
use App\BatchAmmendment;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\DB;

class AmendmentViewer extends Component
{
    use WithPagination;

    // Customer Data
    public $customerId;
    public $customer;
    
    // Search and Filter
    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->loadCustomerData();
    }

    public function loadCustomerData()
    {
        $this->customer = CRMCustomer::findOrFail($this->customerId);
    }

    public function getAmendmentsProperty()
    {
        $query = BatchAmmendment::whereHas('sampleHeader', function($query) {
                $query->where('crm_customer_id', $this->customerId);
            })
            ->with(['sampleHeader'])
            ->when($this->search, function ($query) {
                $query->where('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('sampleHeader', function($q) {
                          $q->where('batch_code', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->statusFilter, function ($query) {
                $query->whereHas('sampleHeader', function($q) {
                    $q->where('status', $this->statusFilter);
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($this->perPage);
    }

    public function viewAmendment($amendmentId)
    {
        $amendment = BatchAmmendment::with(['sampleHeader'])->findOrFail($amendmentId);
        
        // You can implement a modal or redirect to show amendment details
        $this->message = 'Amendment details: ' . $amendment->reason;
        $this->messageType = 'info';
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.amendments.amendment-viewer', [
            'amendments' => $this->amendments
        ]);
    }
}