<?php

namespace App\Services\Sampleworkflow;

use App\Models\SubmissionFormElement;

/**
 * Maps SubmissionForm schema elements to the field shape expected by
 * livewire.sampleworkflow.test-request-field-render.
 */
final class WalkInTrfFieldMapper
{
    /**
     * @return array{name: string, label: string, type: string, required: bool, readonly: bool, options: mixed}
     */
    public function toField(SubmissionFormElement $element): array
    {
        $elementType = (string) ($element->element_type ?? 'text');

        $name = (string) ($element->name ?? '');

        $type = match ($elementType) {
            'client_contact_select' => 'client_contact_select',
            'customer_sample_point_select' => 'customer_sample_point_select',
            default => match ($name) {
                'contact_person' => 'client_contact_select',
                'sampling_location', 'sampling_point' => 'customer_sample_point_select',
                'sampling_time' => 'time',
                'method_of_sampling', 'test_category', 'test_requirements' => 'checkbox',
                default => $elementType,
            },
        };
        $label = (string) ($element->label ?? $element->name ?? '');

        if ($name === 'analysis_type_id') {
            $label = 'Sample Type';
        }

        $readonly = (bool) ($element->is_readonly ?? false);

        if (in_array($name, ['customer_name', 'client_name', 'customer', 'client'], true)) {
            $readonly = false;
        }

        return [
            'name' => $name,
            'label' => $label,
            'type' => $type,
            'required' => (bool) ($element->is_required ?? false),
            'readonly' => $readonly,
            'options' => $element->options ?? [],
        ];
    }
}
