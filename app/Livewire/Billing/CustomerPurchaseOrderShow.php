<?php

namespace App\Livewire\Billing;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\SampleDetails;
use App\SampleHeader;
use Livewire\Component;

class CustomerPurchaseOrderShow extends Component
{
    public string $purchaseOrderId = '';

    public ?CustomerPurchaseOrder $purchaseOrder = null;

    public function mount(string $id): void
    {
        $this->purchaseOrderId = $id;
        $this->loadPurchaseOrder();
    }

    protected function loadPurchaseOrder(): void
    {
        $this->purchaseOrder = CustomerPurchaseOrder::query()
            ->with([
                'customer',
                'quotation',
                'uploader',
                'enquiry.submissionFormInstance.submissionForm',
                'enquiry.batch.samples',
            ])
            ->findOrFail($this->purchaseOrderId);
    }

    /**
     * @return \Illuminate\Support\Collection<int, SampleHeader>
     */
    public function getJobsProperty()
    {
        $enquiry = $this->purchaseOrder?->enquiry;
        if ($enquiry === null) {
            return collect();
        }

        $jobs = collect();

        if ($enquiry->batch !== null) {
            $jobs->push($enquiry->batch);
        }

        $quotationId = $this->purchaseOrder->quotation_header_id
            ?? $enquiry->accepted_quotation_header_id
            ?? $enquiry->current_quotation_header_id;

        if (filled($quotationId)) {
            $fromQuote = SampleHeader::query()
                ->with('samples')
                ->where('quote_id', (string) $quotationId)
                ->get();
            $jobs = $jobs->merge($fromQuote);
        }

        return $jobs->unique('id')->values();
    }

    /**
     * @return \Illuminate\Support\Collection<int, SampleDetails>
     */
    public function getSamplesProperty()
    {
        return $this->jobs
            ->flatMap(fn (SampleHeader $job) => $job->relationLoaded('samples') ? $job->samples : $job->samples()->get())
            ->unique('id')
            ->values();
    }

    public function render()
    {
        return view('livewire.billing.customer-purchase-order-show', [
            'jobs' => $this->jobs,
            'samples' => $this->samples,
        ]);
    }
}
