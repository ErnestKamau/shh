<?php

namespace App\Services\TestRequestForm;

use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestFormInstance;
use App\Services\Commercial\CommercialEnquiryFieldMapper;

final class TrfCheckInMetadataService
{
    public function __construct(
        private TestRequestFormDataMapper $dataMapper,
        private CommercialEnquiryFieldMapper $enquiryFieldMapper,
    ) {}

    /**
     * @return array<string, string>
     */
    public function emptyFields(): array
    {
        return [
            'statement_of_conformity' => '',
            'sampled_by' => '',
            'customer_rep_signature' => '',
            'customer_rep_contact' => '',
            'remarks' => '',
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, string>
     */
    public function hydrateFromFormData(array $formData): array
    {
        $normalized = $this->dataMapper->normalizeFormData($formData);
        $fields = $this->emptyFields();

        foreach (array_keys($fields) as $key) {
            $value = $normalized[$key] ?? $formData[$key] ?? null;
            if ($value !== null && $value !== '') {
                $fields[$key] = (string) $value;
            }
        }

        if ($fields['customer_rep_signature'] === '') {
            $legacy = $normalized['customer_rep_name'] ?? $formData['customer_rep_name'] ?? '';
            if (is_string($legacy) && str_starts_with($legacy, 'data:image')) {
                $fields['customer_rep_signature'] = $legacy;
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, string>  $metadata
     */
    public function persistForInstance(
        TestRequestFormInstance $trfi,
        ?SampleSubmissionRequest $enquiry,
        array $metadata,
    ): void {
        $formData = is_array($trfi->form_data) ? $trfi->form_data : [];
        $merged = array_merge($formData, array_filter($metadata, fn (string $v): bool => $v !== ''));

        $trfi->update([
            'form_data' => $this->dataMapper->normalizeFormData($merged),
        ]);

        if ($enquiry !== null) {
            $fresh = $trfi->fresh();
            $this->enquiryFieldMapper->applyHeaderFieldsFromFormData(
                $enquiry,
                is_array($fresh?->form_data) ? $fresh->form_data : $merged,
            );
            $enquiry->save();
        }
    }
}
