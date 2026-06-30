<?php

namespace App\Services\SubmissionForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use Illuminate\Support\Collection;

/**
 * Normalizes SubmissionFormInstance element values into canonical field maps
 * for enquiry sync, PDF generation, and walk-in capture.
 */
final class SubmissionFormValueNormalizer
{
    /**
     * @return array<string, mixed>
     */
    public function valuesMapFromInstance(SubmissionFormInstance $instance, ?SubmissionForm $form = null): array
    {
        $instance->loadMissing(['values.element', 'submissionForm.sections.elementHolders.elements']);
        $form ??= $instance->submissionForm;

        if ($form === null) {
            return [];
        }

        $form->loadMissing(['sections.elementHolders.elements', 'sampleTypes']);

        $raw = $this->extractRawValues($instance);
        $raw = $this->attachRowsFromSections($instance, $form, $raw);

        return $this->normalizeFormData($raw);
    }

    /**
     * Build a request-compatible flat map from Livewire field state.
     *
     * @param  array<string, mixed>  $fieldValues
     * @return array<string, mixed>
     */
    public function toRequestPayload(array $fieldValues): array
    {
        return $this->normalizeFormData($fieldValues);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractRawValues(SubmissionFormInstance $instance): array
    {
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
        foreach (app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm) as $section) {
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
            $rows = $this->applyFoodSampleTypeAliasesToRows($rows);
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
        foreach (app(SubmissionFormSchemaHelper::class)->uniqueSections($instance->submissionForm) as $section) {
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

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    private function normalizeFormData(array $raw): array
    {
        $aliases = $this->scalarAliases();
        $rowAliases = $this->rowFieldAliases();
        $normalized = [];

        foreach ($raw as $key => $value) {
            $canonicalKey = $aliases[$key] ?? $rowAliases[$key] ?? $key;

            if ($canonicalKey === 'sample_rows' && is_array($value)) {
                $normalized['sample_rows'] = $this->normalizeSampleRows($value, $rowAliases);

                continue;
            }

            if (is_array($value) && $this->looksLikeIndexedRowField($value)) {
                $rows = $this->rowsFromIndexedField($rowAliases[$key] ?? $key, $value);
                $existingRows = is_array($normalized['sample_rows'] ?? null) ? $normalized['sample_rows'] : [];
                $normalized['sample_rows'] = $this->mergeSampleRows($existingRows, $rows);

                continue;
            }

            if (isset($normalized[$canonicalKey]) && $normalized[$canonicalKey] !== '' && $normalized[$canonicalKey] !== null) {
                continue;
            }

            $normalized[$canonicalKey] = $value;
        }

        return $normalized;
    }

    /**
     * @return array<string, string>
     */
    private function scalarAliases(): array
    {
        $config = config('test_request_form_fields.scalar_aliases', []);

        return is_array($config) ? $config : [];
    }

    /**
     * @return array<string, string>
     */
    private function rowFieldAliases(): array
    {
        $config = config('test_request_form_fields.row_field_aliases', []);

        return is_array($config) ? $config : [];
    }

    /**
     * @param  list<array<string, mixed>>|array<int, mixed>  $rows
     * @return list<array<string, mixed>>
     */
    private function normalizeSampleRows(array $rows, array $rowAliases): array
    {
        $normalized = [];

        foreach (array_values($rows) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $canonicalKey = $rowAliases[(string) $key] ?? (string) $key;
                if ($canonicalKey === 'chemical_analysis') {
                    $canonicalKey = 'chemistry';
                }
                $normalizedRow[$canonicalKey] = $value;
            }

            $normalizedRow = $this->normalizeTestCategoryOnRow($normalizedRow);
            $normalizedRow = $this->applyFoodSampleTypeAliasToRow($normalizedRow);

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

        if (isset($row['test_requirements']) && is_string($row['test_requirements'])) {
            $decoded = json_decode($row['test_requirements'], true);
            if (is_array($decoded)) {
                $row['test_requirements'] = $decoded;
            } else {
                $selected = strtolower(trim($row['test_requirements']));
                if ($selected !== '') {
                    $row['test_requirements'] = [
                        'microbiology' => $selected === 'microbiology',
                        'legionella' => $selected === 'legionella',
                        'chemistry' => $selected === 'chemistry',
                    ];
                    if ($category === '') {
                        $category = $selected;
                    }
                }
            }
        }

        if ($category === 'chemical_analysis' || $category === 'chemical') {
            $category = 'chemistry';
        }

        if ($category === '' && isset($row['test_requirements'])) {
            $reqs = $row['test_requirements'];
            if (is_array($reqs)) {
                if (! empty($reqs['microbiology'])) {
                    $category = 'microbiology';
                } elseif (! empty($reqs['legionella'])) {
                    $category = 'legionella';
                } elseif (! empty($reqs['chemistry'])) {
                    $category = 'chemistry';
                }
            }
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
            $rows[(int) $index][$fieldName] = $value;
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
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function applyFoodSampleTypeAliasesToRows(array $rows): array
    {
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rows[$index] = $this->applyFoodSampleTypeAliasToRow($row);
        }

        return $rows;
    }

    /**
     * Walk-in food TRF stores Raw/Cooked/Ready To Eat in analysis_type_id for persistence.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function applyFoodSampleTypeAliasToRow(array $row): array
    {
        if (trim((string) ($row['sample_type'] ?? '')) !== '') {
            return $row;
        }

        $analysisType = trim((string) ($row['analysis_type_id'] ?? ''));
        if ($analysisType !== '' && app(TrfDocumentCodeForSampleType::class)->isFoodSampleTypeLabel($analysisType)) {
            $row['sample_type'] = $analysisType;
        }

        return $row;
    }
}
