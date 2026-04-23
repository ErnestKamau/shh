<?php

namespace App\Services;

use App\Models\SupportingDocumentElement;
use App\Models\SupportingDocumentInstance;
use App\Models\SupportingDocumentInstanceValue;
use App\Support\SupportingDocumentElementValidation;
use Illuminate\Support\Collection;

class SupportingDocumentInstanceFormService
{
    /**
     * @param  Collection<int, SupportingDocumentElement>  $elements
     * @return array<string, array<int, string>>
     */
    public function buildSubmitRules(Collection $elements): array
    {
        $rules = [];

        foreach ($elements as $element) {
            if ($element->element_type === 'static_text') {
                continue;
            }

            if ($element->element_type === 'paragraph_template') {
                foreach (SupportingDocumentElementValidation::paragraphPlaceholderNames($element->default_value) as $placeholder) {
                    $fieldRules = [];
                    $fieldRules[] = $element->is_required ? 'required' : 'nullable';
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:5000';
                    $rules['values.' . $element->id . '.' . $placeholder] = $fieldRules;
                }

                continue;
            }

            if (empty($element->name)) {
                continue;
            }

            $rules['values.' . $element->id] = SupportingDocumentElementValidation::rulesForElement($element);
        }

        return $rules;
    }

    /**
     * @param  Collection<int, SupportingDocumentElement>  $elements
     * @param  array<string, mixed>  $values
     */
    public function syncValuesFromInput(SupportingDocumentInstance $instance, Collection $elements, array $values): void
    {
        $instance->values()->delete();

        foreach ($elements as $element) {
            if ($element->element_type === 'static_text') {
                continue;
            }

            if ($element->element_type === 'paragraph_template') {
                $submitted = $values[$element->id] ?? [];
                if (! is_array($submitted)) {
                    $submitted = [];
                }

                SupportingDocumentInstanceValue::create([
                    'supporting_document_instance_id' => $instance->id,
                    'supporting_document_element_id' => $element->id,
                    'value' => json_encode($submitted, JSON_THROW_ON_ERROR),
                ]);

                continue;
            }

            if (! array_key_exists($element->id, $values)) {
                continue;
            }

            $raw = $values[$element->id];
            if ($raw === null || $raw === '') {
                continue;
            }

            SupportingDocumentInstanceValue::create([
                'supporting_document_instance_id' => $instance->id,
                'supporting_document_element_id' => $element->id,
                'value' => is_array($raw) ? json_encode($raw, JSON_THROW_ON_ERROR) : (string) $raw,
            ]);
        }
    }
}
