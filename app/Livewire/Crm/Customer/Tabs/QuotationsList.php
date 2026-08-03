<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\QuotationHeader;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuotationsList extends BaseCrmComponent
{
    use AuthorizesRequests;
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

    public function openCreateEnquiryWizard(string $quotationId): void
    {
        $this->authorize('laboratory.components.quotation.add');

        $ownsQuote = QuotationHeader::query()
            ->where('crm_customer_id', $this->customer->id)
            ->whereKey($quotationId)
            ->exists();

        if (! $ownsQuote) {
            $this->showError('That quotation does not belong to this customer.');

            return;
        }

        $this->dispatch('open-create-enquiry-from-quotation', quotationId: $quotationId);
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
