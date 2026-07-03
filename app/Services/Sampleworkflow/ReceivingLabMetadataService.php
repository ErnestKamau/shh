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
     * @return array<string, mixed>
     */
    public function emptyFields(): array
    {
        $fields = [];

        foreach ($this->collectionInfoFieldDefinitions() as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $fields[$name] = $this->emptyValueForField($field);
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $formData
     * @return array<string, mixed>
     */
    public function hydrateFromFormData(array $formData, ?SubmissionFormInstance $instance = null): array
    {
        $normalized = $this->valueNormalizer->toRequestPayload($formData);
        $fields = $this->emptyFields();

        foreach ($this->collectionInfoFieldDefinitions() as $field) {
            $name = (string) ($field['name'] ?? '');
            if ($name === '') {
                continue;
            }

            if (($field['type'] ?? '') === 'checkbox') {
                $fields[$name] = $this->hydrateCheckboxField(
                    $field,
                    $normalized[$name] ?? $formData[$name] ?? null,
                    $instance,
                );

                continue;
            }

            $value = $normalized[$name] ?? $formData[$name] ?? null;
            if ($value !== null && $value !== '') {
                $fields[$name] = (string) $value;
            }
        }

        return $fields;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function persistForInstance(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $enquiry,
        array $metadata,
    ): void {
        $instance->loadMissing(['values.element', 'submissionForm.sections.elementHolders.elements']);

        foreach ($metadata as $fieldName => $value) {
            if ($this->isEmptyPersistValue($value)) {
                continue;
            }

            $fieldDef = $this->fieldDefinitionByName((string) $fieldName);
            if (is_array($value) && ($fieldDef['type'] ?? '') === 'checkbox') {
                $this->upsertCheckboxSelection($instance, (string) $fieldName, $value);

                continue;
            }

            $this->upsertScalarValue($instance, (string) $fieldName, (string) $value);
        }

        if ($enquiry !== null) {
            $instance->refresh()->loadMissing(['values.element']);
            $normalized = $this->valueNormalizer->valuesMapFromInstance($instance);
            $this->enquiryFieldMapper->applyHeaderFieldsFromFormData($enquiry, $normalized);
            $enquiry->save();
        }
    }

    /**
     * @return list<array{name: string, label: string, type: string, placeholder?: string, options?: list<array{value: string, label: string}>}>
     */
    private function collectionInfoFieldDefinitions(): array
    {
        $fields = config('test_request_form_fields.receive_check_in_collection_fields', []);

        return is_array($fields) ? $fields : [];
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, bool>|string
     */
    private function emptyValueForField(array $field): array|string
    {
        if (($field['type'] ?? '') === 'checkbox') {
            return $this->emptyCheckboxMap($this->optionValues($field));
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $field
     * @return list<string>
     */
    private function optionValues(array $field): array
    {
        return array_values(array_filter(array_map(
            static fn (array $opt): string => (string) ($opt['value'] ?? ''),
            is_array($field['options'] ?? null) ? $field['options'] : [],
        )));
    }

    /**
     * @param  list<string>  $optionValues
     * @return array<string, bool>
     */
    private function emptyCheckboxMap(array $optionValues): array
    {
        $map = [];
        foreach ($optionValues as $option) {
            $map[$option] = false;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, bool>
     */
    private function hydrateCheckboxField(array $field, mixed $value, ?SubmissionFormInstance $instance): array
    {
        $optionValues = $this->optionValues($field);
        $map = $this->emptyCheckboxMap($optionValues);
        $fieldName = (string) ($field['name'] ?? '');

        if (is_array($value)) {
            if ($this->isAssociativeSelectionMap($value)) {
                foreach ($value as $key => $selected) {
                    if (array_key_exists((string) $key, $map)) {
                        $map[(string) $key] = (bool) $selected;
                    }
                }
            } else {
                foreach ($value as $selected) {
                    $key = (string) $selected;
                    if (array_key_exists($key, $map)) {
                        $map[$key] = true;
                    }
                }
            }
        } elseif (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $this->hydrateCheckboxField($field, $decoded, $instance);
            }

            foreach (array_filter(array_map('trim', explode(',', $value))) as $selected) {
                if (array_key_exists($selected, $map)) {
                    $map[$selected] = true;
                }
            }
        }

        if ($instance !== null && $fieldName !== '') {
            $fromInstance = $this->readCheckboxSelectionFromInstance($instance, $fieldName, $optionValues);
            foreach ($fromInstance as $key => $selected) {
                if ($selected) {
                    $map[$key] = true;
                }
            }
        }

        return $map;
    }

    /**
     * @param  list<string>  $optionValues
     * @return array<string, bool>
     */
    private function readCheckboxSelectionFromInstance(
        SubmissionFormInstance $instance,
        string $fieldName,
        array $optionValues,
    ): array {
        $element = $this->findElement($instance, $fieldName);
        if ($element === null) {
            return $this->emptyCheckboxMap($optionValues);
        }

        $map = $this->emptyCheckboxMap($optionValues);

        foreach ($instance->values->where('submission_form_element_id', $element->id) as $row) {
            if ($row->array_index !== null) {
                $key = (string) $row->value;
                if (array_key_exists($key, $map)) {
                    $map[$key] = true;
                }

                continue;
            }

            if (! is_string($row->value) || $row->value === '') {
                continue;
            }

            $decoded = json_decode($row->value, true);
            if (! is_array($decoded)) {
                continue;
            }

            $hydrated = $this->hydrateCheckboxField(
                ['name' => $fieldName, 'type' => 'checkbox', 'options' => array_map(
                    static fn (string $value): array => ['value' => $value, 'label' => $value],
                    $optionValues,
                )],
                $decoded,
                null,
            );

            foreach ($hydrated as $key => $selected) {
                if ($selected) {
                    $map[$key] = true;
                }
            }
        }

        return $map;
    }

    /**
     * @param  array<string, bool>  $selectionMap
     */
    private function upsertCheckboxSelection(SubmissionFormInstance $instance, string $fieldName, array $selectionMap): void
    {
        $element = $this->findElement($instance, $fieldName);
        if ($element === null) {
            return;
        }

        $selected = array_values(array_map(
            static fn (string $key): string => $key,
            array_keys(array_filter($selectionMap, static fn (mixed $selected): bool => (bool) $selected)),
        ));

        $instance->values()
            ->where('submission_form_element_id', $element->id)
            ->delete();

        foreach ($selected as $index => $value) {
            $instance->values()->create([
                'submission_form_element_id' => $element->id,
                'array_index' => $index,
                'value' => $value,
            ]);
        }
    }

    private function upsertScalarValue(SubmissionFormInstance $instance, string $fieldName, string $value): void
    {
        $element = $this->findElement($instance, $fieldName);
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

    private function findElement(SubmissionFormInstance $instance, string $fieldName): ?object
    {
        return $instance->submissionForm
            ?->sections
            ->flatMap(fn ($section) => $section->elementHolders)
            ->flatMap(fn ($holder) => $holder->elements)
            ->firstWhere('name', $fieldName);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function fieldDefinitionByName(string $fieldName): ?array
    {
        foreach ($this->collectionInfoFieldDefinitions() as $field) {
            if (($field['name'] ?? '') === $fieldName) {
                return $field;
            }
        }

        return null;
    }

    private function isEmptyPersistValue(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (! is_array($value)) {
            return false;
        }

        return array_filter($value, static fn (mixed $item): bool => (bool) $item) === [];
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function isAssociativeSelectionMap(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        return array_keys($values) !== range(0, count($values) - 1);
    }
}
