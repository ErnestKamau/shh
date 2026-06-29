<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquiryFieldMapper;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;

final class ReceivingLabMetadataService
{
    public function __construct(
        private SubmissionFormValueNormalizer $valueNormalizer,
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
        $normalized = $this->valueNormalizer->toRequestPayload($formData);
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
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $metadata,
    ): void {
        $instance->loadMissing(['values.element', 'submissionForm.sections.elementHolders.elements']);

        foreach (array_filter($metadata, fn (string $v): bool => $v !== '') as $fieldName => $value) {
            $this->upsertScalarValue($instance, (string) $fieldName, (string) $value);
        }

        if ($enquiry !== null) {
            $instance->refresh()->loadMissing(['values.element']);
            $normalized = $this->valueNormalizer->valuesMapFromInstance($instance);
            $this->enquiryFieldMapper->applyHeaderFieldsFromFormData($enquiry, $normalized);
            $enquiry->save();
        }
    }

    private function upsertScalarValue(SubmissionFormInstance $instance, string $fieldName, string $value): void
    {
        $element = $instance->submissionForm
            ?->sections
            ->flatMap(fn ($section) => $section->elementHolders)
            ->flatMap(fn ($holder) => $holder->elements)
            ->firstWhere('name', $fieldName);

        if ($element === null) {
            return;
        }

        $instance->values()->updateOrCreate(
            [
                'submission_form_element_id' => $element->id,
                'array_index' => null,
            ],
            ['value' => $value],
        );
    }
}
