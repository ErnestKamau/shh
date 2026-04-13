<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\QuotationHeader;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class QuotationsList extends BaseCrmComponent
{
    public CRMCustomer $customer;

    public int $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function getQuotesProperty(): LengthAwarePaginator
    {
        return QuotationHeader::where('crm_customer_id', $this->customer->id)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function placeholder(): string
    {
        return '<div class="d-flex justify-content-center align-items-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.quotations-list');
    }
}
