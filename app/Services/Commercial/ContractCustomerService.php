<?php

namespace App\Services\Commercial;

use App\Models\Billing\PricelistCustomer;
use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestFormInstance;
use Carbon\Carbon;

final class ContractCustomerService
{
    public function hasContractPricelist(?string $customerId): bool
    {
        if ($customerId === null || $customerId === '') {
            return false;
        }

        return PricelistCustomer::query()->where('customer_id', $customerId)->exists();
    }

    public function isScheduledEnquiry(SampleSubmissionRequest $enquiry): bool
    {
        return $this->isScheduledChannel((string) ($enquiry->source_channel ?? ''));
    }

    public function isScheduledChannel(string $sourceChannel): bool
    {
        return strtolower(trim($sourceChannel)) === TestRequestFormInstance::CHANNEL_SCHEDULED;
    }

    /**
     * Active CRM sampling contract window (managed outside billing pricelist assignment).
     */
    public function hasActiveSamplingContract(?string $customerId, ?Carbon $asOf = null): bool
    {
        if ($customerId === null || $customerId === '') {
            return false;
        }

        $customer = CRMCustomer::query()->find($customerId);
        if ($customer === null) {
            return false;
        }

        $asOf ??= Carbon::today();
        $validFrom = $customer->contract_valid_from;
        $validTo = $customer->contract_valid_to;

        if ($validFrom === null && $validTo === null) {
            return false;
        }

        if ($validFrom !== null && $asOf->lt($validFrom->startOfDay())) {
            return false;
        }

        if ($validTo !== null && $asOf->gt($validTo->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Skip walk-in quotation / PO gates: all scheduled enquiries, or CRM sampling contract window.
     */
    public function bypassesCommercialQuotationGate(SampleSubmissionRequest $enquiry): bool
    {
        if ($this->isScheduledEnquiry($enquiry)) {
            return true;
        }

        return $this->hasActiveSamplingContract((string) $enquiry->crm_customer_id);
    }

    /**
     * @deprecated Use bypassesCommercialQuotationGate() or isScheduledEnquiry().
     */
    public function isScheduledContractEnquiry(SampleSubmissionRequest $enquiry): bool
    {
        return $this->bypassesCommercialQuotationGate($enquiry);
    }
}
