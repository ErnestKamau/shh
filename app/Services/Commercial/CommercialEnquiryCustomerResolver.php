<?php

namespace App\Services\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestFormInstance;

final class CommercialEnquiryCustomerResolver
{
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

        return $enquiry->fresh(['customer', 'contact', 'testRequestFormInstance']);
    }

    public function customerNameFromEnquiry(SampleSubmissionRequest $enquiry): string
    {
        $enquiry->loadMissing([
            'customer',
            'submissionFormInstance.testRequestFormInstance',
            'testRequestFormInstance',
        ]);

        $name = trim((string) ($enquiry->customer?->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        foreach ([
            $enquiry->testRequestFormInstance,
            $enquiry->submissionFormInstance?->testRequestFormInstance,
        ] as $trfi) {
            $fromTrfi = $this->customerNameFromTrfi($trfi);
            if ($fromTrfi !== '') {
                return $fromTrfi;
            }
        }

        return '';
    }

    private function customerNameFromTrfi(?TestRequestFormInstance $trfi): string
    {
        if ($trfi === null) {
            return '';
        }

        $formData = is_array($trfi->form_data) ? $trfi->form_data : [];

        foreach (['customer_name', 'client_name', 'customer', 'client'] as $key) {
            $candidate = trim((string) ($formData[$key] ?? ''));
            if ($candidate !== '') {
                return $candidate;
            }
        }

        return '';
    }
}
