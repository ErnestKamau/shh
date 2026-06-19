<?php

namespace App\Services\TestRequestForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestForm;
use App\Models\TestRequestFormInstance;

final class TestRequestFormDataMapper
{
    /** @var array<string, string> */
    private array $scalarAliases;

    /** @var array<string, string> */
    private array $rowFieldAliases;

    public function __construct()
    {
        $config = config('test_request_form_fields', []);
        $this->scalarAliases = is_array($config['scalar_aliases'] ?? null) ? $config['scalar_aliases'] : [];
        $this->rowFieldAliases = is_array($config['row_field_aliases'] ?? null) ? $config['row_field_aliases'] : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizeFormData(array $raw, ?TestRequestForm $template = null): array
    {
        unset($template);

        $normalized = [];

        foreach ($raw as $key => $value) {
            $canonicalKey = $this->canonicalScalarKey((string) $key);

            if ($canonicalKey === 'sample_rows' && is_array($value)) {
                $normalized['sample_rows'] = $this->normalizeSampleRows($value);

                continue;
            }

            if (is_array($value) && $this->looksLikeIndexedRowField($value)) {
                $rows = $this->rowsFromIndexedField($canonicalKey, $value);
                $existingRows = is_array($normalized['sample_rows'] ?? null) ? $normalized['sample_rows'] : [];
                $normalized['sample_rows'] = $this->mergeSampleRows($existingRows, $rows);

                continue;
            }

            if (isset($normalized[$canonicalKey]) && $normalized[$canonicalKey] !== '' && $normalized[$canonicalKey] !== null) {
                continue;
            }

            $normalized[$canonicalKey] = $value;
        }

        if (! isset($normalized['sample_rows']) && isset($raw['rows_section'])) {
            $normalized['sample_rows'] = $this->normalizeSampleRows(
                is_array($raw['rows_section']) ? $raw['rows_section'] : []
            );
        }

        return $normalized;
    }

    /**
     * @return array<string, mixed>
     */
    public function fromSubmissionFormInstance(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm,
        ?TestRequestForm $template = null,
    ): array {
        $instance->loadMissing(['values.element', 'submissionForm.sections.elementHolders.elements']);
        $submissionForm->loadMissing(['sections.elementHolders.elements', 'sampleTypes']);

        $raw = $this->extractRawValues($instance, $submissionForm);
        $raw = $this->attachRowsFromSections($instance, $submissionForm, $raw);

        return $this->normalizeFormData($raw, $template);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSubmissionFormRequestData(array $formData, ?TestRequestForm $template = null): array
    {
        return TestRequestFormInstance::mapToSubmissionFormRequestData(
            $this->normalizeFormData($formData, $template),
            $template,
        );
    }

    /**
     * Map canonical TRFI keys to portal SubmissionForm element names (inverse of scalar aliases where applicable).
     *
     * @return array<string, mixed>
     */
    public function toSubmissionFormElementValues(array $canonicalData, SubmissionForm $form): array
    {
        $form->loadMissing(['sections.elementHolders.elements']);

        $elementNames = [];
        foreach ($form->sections as $section) {
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name !== '') {
                        $elementNames[$name] = true;
                    }
                }
            }
        }

        $mapped = [];
        $normalized = $this->normalizeFormData($canonicalData);

        foreach ($normalized as $key => $value) {
            if ($key === 'sample_rows') {
                continue;
            }

            $submissionKey = $this->submissionFormKeyForCanonical((string) $key, array_keys($elementNames));
            if ($submissionKey !== null) {
                $mapped[$submissionKey] = $value;
            }
        }

        if (isset($normalized['sample_rows']) && is_array($normalized['sample_rows'])) {
            foreach ($normalized['sample_rows'] as $rowIndex => $row) {
                if (! is_array($row)) {
                    continue;
                }

                foreach ($row as $rowKey => $rowValue) {
                    $submissionKey = $this->submissionFormKeyForCanonical((string) $rowKey, array_keys($elementNames));
                    if ($submissionKey === null) {
                        continue;
                    }

                    if (! isset($mapped[$submissionKey]) || ! is_array($mapped[$submissionKey])) {
                        $mapped[$submissionKey] = [];
                    }

                    $mapped[$submissionKey][(int) $rowIndex] = $rowValue;
                }
            }

            foreach ($mapped as $field => $values) {
                if (is_array($values)) {
                    $mapped[$field] = array_values($values);
                }
            }
        }

        return $mapped;
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRawValues(SubmissionFormInstance $instance, SubmissionForm $submissionForm): array
    {
        unset($submissionForm);

        $rowElementNames = $this->rowElementNames($instance);

        $formData = [];
        foreach ($instance->values as $value) {
            $fieldName = $value->element?->name ?? $value->element_name ?? null;
            if (! $fieldName) {
                continue;
            }

            if ($value->array_index !== null) {
                if (in_array((string) $fieldName, $rowElementNames, true)) {
                    $existing = $formData[$fieldName] ?? [];
                    if (! is_array($existing)) {
                        $existing = [];
                    }
                    $existing[(int) $value->array_index] = $value->value;
                    $formData[$fieldName] = $existing;
                }

                continue;
            }

            $formData[$fieldName] = $value->value;
        }

        return $formData;
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function attachRowsFromSections(
        SubmissionFormInstance $instance,
        SubmissionForm $submissionForm,
        array $raw,
    ): array {
        if (isset($raw['sample_rows']) && is_array($raw['sample_rows'])) {
            return $raw;
        }

        $rows = [];
        foreach ($submissionForm->sections as $section) {
            if ((string) ($section->section_type ?? '') !== 'rows_section') {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name === '') {
                        continue;
                    }

                    $elementValues = $instance->values
                        ->where('submission_form_element_id', $element->id)
                        ->sortBy('array_index');

                    foreach ($elementValues as $value) {
                        $rowIndex = (int) ($value->array_index ?? 0);
                        if (! isset($rows[$rowIndex])) {
                            $rows[$rowIndex] = [];
                        }
                        $rows[$rowIndex][$name] = $value->value;
                    }
                }
            }
        }

        if ($rows !== []) {
            ksort($rows);
            $raw['sample_rows'] = array_values($rows);
        }

        return $raw;
    }

    /**
     * @return list<string>
     */
    private function rowElementNames(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing('submissionForm.sections.elementHolders.elements');

        $names = [];
        foreach ($instance->submissionForm->sections as $section) {
            if ((string) ($section->section_type ?? '') !== 'rows_section') {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    $name = (string) ($element->name ?? '');
                    if ($name !== '') {
                        $names[] = $name;
                    }
                }
            }
        }

        return $names;
    }

    private function canonicalScalarKey(string $key): string
    {
        return $this->scalarAliases[$key] ?? $this->rowFieldAliases[$key] ?? $key;
    }

    /**
     * @param  list<array<string, mixed>>|array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeSampleRows(array $rows): array
    {
        $normalized = [];

        foreach (array_values($rows) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $canonicalKey = $this->rowFieldAliases[(string) $key] ?? $this->scalarAliases[(string) $key] ?? (string) $key;

                if ($canonicalKey === 'chemical_analysis') {
                    $canonicalKey = 'chemistry';
                }

                $normalizedRow[$canonicalKey] = $value;
            }

            $normalizedRow = $this->normalizeTestCategoryOnRow($normalizedRow);

            if ($normalizedRow !== []) {
                $normalized[] = $normalizedRow;
            }
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeTestCategoryOnRow(array $row): array
    {
        $category = strtolower(trim((string) ($row['test_category'] ?? '')));

        if ($category === 'chemical_analysis' || $category === 'chemical') {
            $category = 'chemistry';
        }

        if ($category === '') {
            if (filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $category = 'microbiology';
            } elseif (filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $category = 'legionella';
            } elseif (filter_var($row['chemistry'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $category = 'chemistry';
            }
        }

        if ($category !== '') {
            $row['test_category'] = $category;
            $row['microbiology'] = $category === 'microbiology';
            $row['legionella'] = $category === 'legionella';
            $row['chemistry'] = $category === 'chemistry';
            unset($row['chemical_analysis']);
        }

        return $row;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function looksLikeIndexedRowField(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        $keys = array_keys($values);

        return $keys === array_keys(array_values($values));
    }

    /**
     * @param  array<int|string, mixed>  $indexedValues
     * @return list<array<string, mixed>>
     */
    private function rowsFromIndexedField(string $fieldName, array $indexedValues): array
    {
        $rows = [];
        foreach ($indexedValues as $index => $value) {
            $canonicalKey = $this->rowFieldAliases[$fieldName] ?? $fieldName;
            $rows[(int) $index][$canonicalKey] = $value;
        }

        ksort($rows);

        return array_values($rows);
    }

    /**
     * @param  list<array<string, mixed>>  $base
     * @param  list<array<string, mixed>>  $extra
     * @return list<array<string, mixed>>
     */
    private function mergeSampleRows(array $base, array $extra): array
    {
        $merged = $base;

        foreach ($extra as $index => $row) {
            if (! isset($merged[$index])) {
                $merged[$index] = [];
            }

            $merged[$index] = array_merge($merged[$index], $row);
        }

        return array_values($merged);
    }

    /**
     * @param  list<string>  $elementNames
     */
    private function submissionFormKeyForCanonical(string $canonicalKey, array $elementNames): ?string
    {
        if (in_array($canonicalKey, $elementNames, true)) {
            return $canonicalKey;
        }

        foreach ($this->scalarAliases as $alias => $canonical) {
            if ($canonical === $canonicalKey && in_array($alias, $elementNames, true)) {
                return $alias;
            }
        }

        foreach ($this->rowFieldAliases as $alias => $canonical) {
            if ($canonical === $canonicalKey && in_array($alias, $elementNames, true)) {
                return $alias;
            }
        }

        return in_array($canonicalKey, $elementNames, true) ? $canonicalKey : null;
    }
}
