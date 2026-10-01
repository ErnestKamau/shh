<?php

namespace App\Services\Commercial;

use App\Models\Billing\PricelistCustomer;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CrmCustomerContract;
use App\Models\SampleSubmissionRequest;
use Carbon\Carbon;

final class ContractCustomerService
{
    /**
     * True when the customer has an assigned pricelist (pricelist_customers).
     */
    public function hasAssignedPricelist(?string $customerId): bool
    {
        if ($customerId === null || $customerId === '') {
            return false;
        }

        return PricelistCustomer::query()->where('customer_id', $customerId)->exists();
    }

    /**
     * @deprecated Use hasAssignedPricelist().
     */
    public function hasContractPricelist(?string $customerId): bool
    {
        return $this->hasAssignedPricelist($customerId);
    }

    public function isScheduledEnquiry(SampleSubmissionRequest $enquiry): bool
    {
        return $this->isScheduledChannel((string) ($enquiry->source_channel ?? ''));
    }

    public function isScheduledChannel(string $sourceChannel): bool
    {
        return strtolower(trim($sourceChannel)) === CommercialEnquirySyncService::SOURCE_SCHEDULED;
    }

    public function currentContract(?string $customerId): ?CrmCustomerContract
    {
        if ($customerId === null || $customerId === '') {
            return null;
        }

        return CrmCustomerContract::query()
            ->where('crm_customer_id', $customerId)
            ->where('is_current', true)
            ->latest('created_at')
            ->first();
    }

    /**
     * Active CRM sampling contract window (managed outside billing pricelist assignment).
     * Prefers the current contract row; falls back to denormalized customer dates.
     */
    public function hasActiveSamplingContract(?string $customerId, ?Carbon $asOf = null): bool
    {
        if ($customerId === null || $customerId === '') {
            return false;
        }

        $asOf ??= Carbon::today();
        $contract = $this->currentContract($customerId);

        if ($contract !== null) {
            return $this->dateWindowIsActive($contract->valid_from, $contract->valid_to, $asOf);
        }

        $customer = CRMCustomer::query()->find($customerId);
        if ($customer === null) {
            return false;
        }

        return $this->dateWindowIsActive($customer->contract_valid_from, $customer->contract_valid_to, $asOf);
    }

    /**
     * @return array{contract_scope?: string, is_scheduled_sampling?: bool, collection_method?: string}
     */
    public function contractIntakeMetadata(?string $customerId): array
    {
        if ($customerId === null || $customerId === '') {
            return [];
        }

        $contract = $this->currentContract($customerId);
        $customer = null;

        if ($contract === null) {
            $customer = CRMCustomer::query()->find($customerId);
            if ($customer === null) {
                return [];
            }
        }

        $scope = $contract?->contract_scope ?? $customer?->contract_scope;
        $isScheduled = (bool) ($contract?->is_scheduled_sampling ?? $customer?->is_scheduled_sampling ?? false);
        $collectionMethod = $contract?->default_collection_method ?? $customer?->default_collection_method;

        $meta = [];

        if (filled($scope)) {
            $meta['contract_scope'] = (string) $scope;
        }

        $meta['is_scheduled_sampling'] = $isScheduled;

        if (! $isScheduled && filled($collectionMethod)) {
            $meta['collection_method'] = (string) $collectionMethod;
        }

        return $meta;
    }

    /**
     * Skip the walk-in quotation gate: all scheduled enquiries, or CRM sampling contract window.
     * With PO ledger cover enabled this no longer skips the PO: contract enquiries need PO cover
     * (see EnquiryPurchaseOrderService::requirement()).
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

    private function dateWindowIsActive(mixed $validFrom, mixed $validTo, Carbon $asOf): bool
    {
        if ($validFrom === null && $validTo === null) {
            return false;
        }

        if ($validFrom !== null) {
            $from = $validFrom instanceof Carbon ? $validFrom->copy() : Carbon::parse($validFrom);
            if ($asOf->lt($from->startOfDay())) {
                return false;
            }
        }

        if ($validTo !== null) {
            $to = $validTo instanceof Carbon ? $validTo->copy() : Carbon::parse($validTo);
            if ($asOf->gt($to->endOfDay())) {
                return false;
            }
        }

        return true;
    }
}
