<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\SampleHeader;
use App\Livewire\Crm\BaseCrmComponent;

class CustomerOrdersTab extends BaseCrmComponent
{
    use \Livewire\WithPagination;

    public $customer;
    public $search = '';
    public $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function mount($customer)
    {
        $this->customer = $customer;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function exportToExcel()
    {
        $this->checkPermission('CRM.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'orders', $this->search))
            ->download('customer_orders_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function getOrdersProperty()
    {
        return SampleHeader::leftJoin('sample_details as sd', 'sample_headers.id', '=', 'sd.sample_header_id')
            ->join('sample_types as st', 'st.id', 'sample_headers.sample_type_id')
            ->selectRaw('sample_headers.id, sample_headers.batch_code, sample_headers.date_collected, sample_headers.reference_number, sample_headers.document_number, sample_headers.status, count(sd.id) as samples, st.name as sample_type')
            ->where('sample_headers.crm_customer_id', $this->customer->id)
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('sample_headers.batch_code', 'like', '%'.$this->search.'%')
                      ->orWhere('sample_headers.reference_number', 'like', '%'.$this->search.'%')
                      ->orWhere('sample_headers.document_number', 'like', '%'.$this->search.'%');
                });
            })
            ->groupBy('sample_headers.id', 'sample_headers.batch_code', 'sample_headers.date_collected', 'sample_headers.reference_number', 'sample_headers.document_number', 'sample_headers.status', 'st.name')
            ->whereNotIn('sample_headers.status', ["Completed"])
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-orders-tab', [
            'orders' => $this->orders,
        ]);
    }
}

