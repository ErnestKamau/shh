<?php

namespace App\Services\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;

final class CommercialEnquiryCustomerResolver
{
    public function __construct(
        private SubmissionFormValueNormalizer $valueNormalizer,
    ) {}

    public function resolveCustomerId(SampleSubmissionRequest $enquiry): ?string
    {
        if (! empty($enquiry->crm_customer_id)) {
            return (string) $enquiry->crm_customer_id;
        }

        $customerName = $this->customerNameFromEnquiry($enquiry);
        if ($customerName === '') {
            return null;
        }

        $customerId = CRMCustomer::query()
            ->whereRaw('name ILIKE ?', [$customerName])
            ->value('id');

        if ($customerId === null) {
            return null;
        }

        $enquiry->forceFill(['crm_customer_id' => $customerId])->save();

        return (string) $customerId;
    }

    public function persistResolvedCustomer(SampleSubmissionRequest $enquiry): SampleSubmissionRequest
    {
        $this->resolveCustomerId($enquiry);

        return $enquiry->fresh(['customer', 'contact', 'submissionFormInstance']);
    }

    public function customerNameFromEnquiry(SampleSubmissionRequest $enquiry): string
    {
        $enquiry->loadMissing([
            'customer',
            'submissionFormInstance.values.element',
        ]);

        $name = trim((string) ($enquiry->customer?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        if ($enquiry->submissionFormInstance !== null) {
            $formData = $this->valueNormalizer->valuesMapFromInstance($enquiry->submissionFormInstance);

            foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
                $candidate = trim((string) ($formData[$key] ?? ''));
                if ($candidate !== '') {
                    return $candidate;
                }
            }
        }

        return '';
    }
}
