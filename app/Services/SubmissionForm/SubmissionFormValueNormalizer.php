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

        $form->loadMissing(['sections.elementHolders.elements']);

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

            // Legacy batch scalars for fields that are now per-sample rows → index 0.
            if (in_array((string) $fieldName, $rowElementNames, true)) {
                $existing = $formData[$fieldName] ?? [];
                if (! is_array($existing)) {
                    $existing = [];
                }
                if (! array_key_exists(0, $existing) || $existing[0] === null || $existing[0] === '') {
                    $existing[0] = $value->value;
                }
                $formData[$fieldName] = $existing;

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
            if (! SubmissionFormSchemaHelper::sectionUsesSampleCards($section)) {
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
            $anchorCount = 0;
            foreach (SubmissionFormSchemaHelper::sampleRowAnchorFieldNames() as $anchor) {
                $maxIndex = -1;
                foreach ($rows as $rowIndex => $row) {
                    if (isset($row[$anchor]) && $row[$anchor] !== null && $row[$anchor] !== '') {
                        $maxIndex = max($maxIndex, (int) $rowIndex);
                    }
                }
                if ($maxIndex >= 0) {
                    $anchorCount = max($anchorCount, $maxIndex + 1);
                }
            }

            if ($anchorCount > 0) {
                $rows = array_filter(
                    $rows,
                    static fn (array $row, int|string $index): bool => (int) $index < $anchorCount,
                    ARRAY_FILTER_USE_BOTH,
                );
            }

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
            if (! SubmissionFormSchemaHelper::sectionUsesSampleCards($section)) {
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

        // Always include canonical qty fields so walk-in Qty/Unit persists/reads even when
        // the builder only has legacy number_of_samples.
        foreach (['sample_quantity', 'sample_quantity_unit', 'number_of_samples'] as $qtyField) {
            $names[] = $qtyField;
        }

        return array_values(array_unique($names));
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
        $indexedFields = [];

        foreach ($raw as $key => $value) {
            $canonicalKey = $aliases[$key] ?? $rowAliases[$key] ?? $key;

            if ($canonicalKey === 'sample_rows' && is_array($value)) {
                $normalized['sample_rows'] = $this->normalizeSampleRows($value, $rowAliases);

                continue;
            }

            if (is_array($value) && $this->hasSequentialKeys($value)) {
                $indexedFields[$canonicalKey] = $value;

                continue;
            }

            if (isset($normalized[$canonicalKey]) && $normalized[$canonicalKey] !== '' && $normalized[$canonicalKey] !== null) {
                continue;
            }

            if (is_array($value)) {
                $selectedKeys = SubmissionFormSchemaHelper::selectedCheckboxKeys($value);
                if ($selectedKeys !== null) {
                    $normalized[$canonicalKey] = implode(',', $selectedKeys);

                    continue;
                }
            }

            $normalized[$canonicalKey] = $value;
        }

        if ($indexedFields !== []) {
            $fromIndexed = $this->sampleRowsFromIndexedFields($indexedFields, $rowAliases);
            $existingRows = is_array($normalized['sample_rows'] ?? null) ? $normalized['sample_rows'] : [];
            $normalized['sample_rows'] = $this->normalizeSampleRows(
                $this->mergeSampleRows($existingRows, $fromIndexed),
                $rowAliases,
            );

            // Keep per-row indexed keys for processFormData persistence.
            foreach ($indexedFields as $field => $values) {
                if (! isset($normalized[$field])) {
                    $normalized[$field] = $this->normalizeIndexedMultiSelectForStorage($field, $values);
                }
            }
        }

        return $normalized;
    }

    /**
     * Build sample_rows using anchor fields for count so flat parameters cannot create N fake samples.
     *
     * @param  array<string, array<int|string, mixed>>  $indexedFields
     * @param  array<string, string>  $rowAliases
     * @return list<array<string, mixed>>
     */
    private function sampleRowsFromIndexedFields(array $indexedFields, array $rowAliases): array
    {
        unset($rowAliases);

        $rowCount = $this->resolveSampleRowCountFromIndexedFields($indexedFields);
        $rows = [];
        for ($i = 0; $i < $rowCount; $i++) {
            $rows[$i] = [];
        }

        foreach ($indexedFields as $field => $values) {
            $normalizedValues = $this->normalizeIndexedMultiSelectForStorage($field, $values);

            if (
                in_array($field, SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames(), true)
                && count($normalizedValues) > $rowCount
                && $this->allScalarValues($normalizedValues)
            ) {
                if (in_array($field, ['parameters', 'parameter'], true)) {
                    // Flat parameter explosion — keep every token, attach to first sample card.
                    $joined = implode(',', array_map(
                        static fn ($value): string => trim((string) $value),
                        array_values($normalizedValues),
                    ));
                    $joined = trim($joined, ',');
                    if ($joined !== '' && isset($rows[0])) {
                        $rows[0][$field] = $joined;
                    }
                } else {
                    // Flat analysis/sample-type list longer than cards — keep one value per card.
                    foreach (array_values($normalizedValues) as $index => $value) {
                        if ($index >= $rowCount) {
                            break;
                        }
                        $rows[$index][$field] = $value;
                    }
                }

                continue;
            }

            foreach ($normalizedValues as $index => $value) {
                $rowIndex = (int) $index;
                if ($rowIndex < 0 || $rowIndex >= $rowCount) {
                    continue;
                }

                if ($field === 'additional_details') {
                    if (is_string($value) && trim($value) !== '') {
                        $decoded = json_decode($value, true);
                        $rows[$rowIndex][$field] = is_array($decoded) ? $decoded : [];
                    } elseif (is_array($value)) {
                        $rows[$rowIndex][$field] = array_values(array_filter(
                            $value,
                            static fn ($row): bool => is_array($row)
                        ));
                    } else {
                        $rows[$rowIndex][$field] = [];
                    }

                    continue;
                }

                if (is_array($value)) {
                    $selectedKeys = SubmissionFormSchemaHelper::selectedCheckboxKeys($value);
                    $rows[$rowIndex][$field] = $selectedKeys !== null
                        ? implode(',', $selectedKeys)
                        : implode(',', array_map('strval', $value));
                } else {
                    $rows[$rowIndex][$field] = $value;
                }
            }
        }

        return array_values($rows);
    }

    /**
     * @param  array<string, array<int|string, mixed>>  $indexedFields
     */
    private function resolveSampleRowCountFromIndexedFields(array $indexedFields): int
    {
        $anchorNames = SubmissionFormSchemaHelper::sampleRowAnchorFieldNames();
        $count = 0;

        foreach ($anchorNames as $anchor) {
            if (! isset($indexedFields[$anchor]) || ! is_array($indexedFields[$anchor])) {
                continue;
            }
            $count = max($count, count($indexedFields[$anchor]));
        }

        if ($count > 0) {
            return $count;
        }

        // No anchors: use nested multi-select row lists (list of arrays), never flat token lists.
        foreach (SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames() as $multiField) {
            if (! isset($indexedFields[$multiField]) || ! is_array($indexedFields[$multiField])) {
                continue;
            }
            $values = $indexedFields[$multiField];
            if ($this->containsArrayValues($values)) {
                $count = max($count, count($values));
            } elseif ($this->allScalarValues($values) && $this->countCsvLikeScalars($values) > 0) {
                $count = max($count, count($values));
            }
        }

        if ($count > 0) {
            return $count;
        }

        foreach ($indexedFields as $values) {
            if (is_array($values)) {
                $count = max($count, count($values));
            }
        }

        return max(1, $count);
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return array<int, mixed>
     */
    private function normalizeIndexedMultiSelectForStorage(string $field, array $values): array
    {
        $out = [];
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $selectedKeys = SubmissionFormSchemaHelper::selectedCheckboxKeys($value);

                if ($selectedKeys !== null) {
                    $out[(int) $index] = implode(',', $selectedKeys);

                    continue;
                }

                $flat = [];
                foreach ($value as $item) {
                    if ($item === null || $item === '') {
                        continue;
                    }
                    $flat[] = (string) $item;
                }
                $out[(int) $index] = in_array($field, SubmissionFormSchemaHelper::sampleRowMultiSelectFieldNames(), true)
                    ? implode(',', $flat)
                    : $value;
            } else {
                $out[(int) $index] = $value;
            }
        }
        ksort($out);

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function hasSequentialKeys(array $values): bool
    {
        if ($values === []) {
            return false;
        }

        return array_keys($values) === array_keys(array_values($values));
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function containsArrayValues(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function allScalarValues(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                return false;
            }
        }

        return $values !== [];
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function countCsvLikeScalars(array $values): int
    {
        $count = 0;
        foreach ($values as $value) {
            if (is_string($value) && str_contains($value, ',')) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param  array<int|string, mixed>  $values
     */
    private function looksLikeIndexedRowField(array $values): bool
    {
        return $this->hasSequentialKeys($values);
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
        $categoryTokens = SubmissionFormSchemaHelper::testCategoryTokens($row['test_category'] ?? null);
        $category = $categoryTokens[0] ?? '';

        if (isset($row['test_requirements']) && is_string($row['test_requirements'])) {
            $decoded = json_decode($row['test_requirements'], true);
            if (is_array($decoded)) {
                $row['test_requirements'] = $decoded;
            } else {
                $selected = SubmissionFormSchemaHelper::testCategoryTokens($row['test_requirements']);
                if ($selected !== []) {
                    $row['test_requirements'] = [
                        'microbiology' => in_array('microbiology', $selected, true),
                        'legionella' => in_array('legionella', $selected, true),
                        'chemistry' => in_array('chemistry', $selected, true),
                    ];
                    if ($categoryTokens === []) {
                        $categoryTokens = $selected;
                        $category = $selected[0];
                    }
                }
            }
        }

        if ($categoryTokens === [] && isset($row['test_requirements'])) {
            $reqs = $row['test_requirements'];
            if (is_array($reqs)) {
                $selected = SubmissionFormSchemaHelper::testCategoryTokens($reqs);
                if ($selected !== []) {
                    $categoryTokens = $selected;
                    $category = $selected[0];
                }
            }
        }

        if ($categoryTokens === []) {
            if (filter_var($row['microbiology'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $categoryTokens[] = 'microbiology';
            }
            if (filter_var($row['legionella'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $categoryTokens[] = 'legionella';
            }
            if (filter_var($row['chemistry'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $categoryTokens[] = 'chemistry';
            }
            $category = $categoryTokens[0] ?? '';
        }

        if ($categoryTokens !== []) {
            $row['test_category'] = implode(',', $categoryTokens);
            $row['microbiology'] = in_array('microbiology', $categoryTokens, true);
            $row['legionella'] = in_array('legionella', $categoryTokens, true);
            $row['chemistry'] = in_array('chemistry', $categoryTokens, true);
        }

        return $row;
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
