<?php

namespace App\Services\SubmissionForm;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\CRM\SamplePoint;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\Lab\AnalysisReferenceLabelResolver;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use Illuminate\Support\Str;

class SubmissionRequestSampleLineService
{
    public function __construct(
        private readonly AnalysisReferenceLabelResolver $referenceLabelResolver,
        private readonly SubmissionFormSchemaHelper $schemaHelper,
        private readonly SubmissionFormValueNormalizer $valueNormalizer,
    ) {}

    /**
     * @return list<array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }>
     */
    public function linesForInstance(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing([
            'submissionForm.sections.elementHolders.elements' => fn ($q) => $q->orderBy('sort_order'),
            'submissionForm.sampleTypes',
            'values.element',
        ]);

        $trfRowLines = $this->deduplicateLines($this->linesFromSampleRowsFormData($instance));
        if ($trfRowLines !== []) {
            return $this->finalizeInstanceLines(
                $instance,
                $this->enrichTrfRowLinesFromRowsSection($instance, $trfRowLines),
            );
        }

        $rowLines = $this->deduplicateLines($this->parseRowsSections($instance));

        if ($this->linesHaveRichSampleDetail($rowLines)) {
            return $this->finalizeInstanceLines($instance, $rowLines);
        }

        if ($rowLines !== []) {
            return $this->finalizeInstanceLines($instance, $rowLines);
        }

        return $this->finalizeInstanceLines($instance, $this->fallbackLinesFromHeader($instance));
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function finalizeInstanceLines(SubmissionFormInstance $instance, array $lines): array
    {
        if ($lines === []) {
            return [];
        }

        return $this->enrichLinesWithSavedParameterValues($instance, $lines);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function enrichLinesWithSavedParameterValues(SubmissionFormInstance $instance, array $lines): array
    {
        $parameterValuesByRow = $this->parameterValuesByRowIndex($instance);
        if ($parameterValuesByRow === []) {
            return $lines;
        }

        foreach ($lines as &$line) {
            $rowIndex = (int) ($line['row_index'] ?? 0);
            $raw = trim((string) ($parameterValuesByRow[$rowIndex] ?? ''));
            if ($raw === '') {
                continue;
            }

            $tokens = $this->referenceLabelResolver->extractTokens($raw);
            if ($tokens === []) {
                continue;
            }

            $this->applyParameterTokensToLine($line, $tokens);

            $resolved = $this->referenceLabelResolver->resolveMixed($tokens);
            if ($resolved !== '') {
                $line['parameter_label'] = $resolved;
            }

            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['parameters'] = $raw;
        }
        unset($line);

        return $lines;
    }

    /**
     * @return array<int, string>
     */
    private function parameterValuesByRowIndex(SubmissionFormInstance $instance): array
    {
        $byRow = [];

        foreach ($instance->values as $value) {
            $element = $value->element;
            if ($element === null || (string) ($element->name ?? '') !== 'parameters') {
                continue;
            }

            $stored = trim((string) ($value->value ?? ''));
            if ($stored === '') {
                continue;
            }

            $byRow[(int) ($value->array_index ?? 0)] = $stored;
        }

        return $byRow;
    }

    /**
     * TRF sample_rows snapshots can omit PARAMETERS selections that live on rows_section values.
     *
     * @param  list<array<string, mixed>>  $trfRowLines
     * @return list<array<string, mixed>>
     */
    private function enrichTrfRowLinesFromRowsSection(SubmissionFormInstance $instance, array $trfRowLines): array
    {
        $sectionLines = $this->deduplicateLines($this->parseRowsSections($instance));
        if ($sectionLines === []) {
            return $trfRowLines;
        }

        $sectionByRow = collect($sectionLines)->keyBy('row_index');

        foreach ($trfRowLines as &$line) {
            $rowIndex = (int) ($line['row_index'] ?? 0);
            /** @var array<string, mixed>|null $sectionLine */
            $sectionLine = $sectionByRow->get($rowIndex);
            if ($sectionLine === null) {
                continue;
            }

            $sectionAttributes = is_array($sectionLine['attributes'] ?? null) ? $sectionLine['attributes'] : [];
            $sectionElementIds = $sectionAttributes['analysis_element_ids'] ?? [];
            if ($sectionElementIds === [] && ! empty($sectionLine['analysis_element_id'])) {
                $sectionElementIds = [(string) $sectionLine['analysis_element_id']];
            }

            if ($sectionElementIds === []) {
                $sectionParameterLabel = trim((string) ($sectionLine['parameter_label'] ?? ''));
                if ($sectionParameterLabel !== '' && ! $this->isTestCategoryLabel($sectionParameterLabel)) {
                    $line['parameter_label'] = $sectionParameterLabel;
                }

                continue;
            }

            $line['analysis_element_id'] = $sectionLine['analysis_element_id'] ?? $sectionElementIds[0];
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['analysis_element_ids'] = array_values(array_map('strval', $sectionElementIds));

            $sectionParameterLabel = trim((string) ($sectionLine['parameter_label'] ?? ''));
            if ($sectionParameterLabel !== '') {
                $line['parameter_label'] = $sectionParameterLabel;
            }
        }
        unset($line);

        return $trfRowLines;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function seedsForAcceptancePrefill(SubmissionFormInstance $instance): array
    {
        $lines = $this->linesForInstance($instance);
        $seeds = [];

        foreach ($lines as $line) {
            if (empty($line['analysis_type_id']) && empty($line['analysis_element_id'])) {
                continue;
            }

            $seeds[] = [
                'row_index' => $line['row_index'] ?? null,
                'sample_type_id' => $line['sample_type_id'],
                'analysis_type_id' => (string) ($line['analysis_type_id'] ?? ''),
                'analysis_element_id' => $line['analysis_element_id'],
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'customer_sample_id' => $line['customer_sample_id'],
                'sample_code_prefix' => $line['sample_code_prefix'] ?? null,
            ];
        }

        return $seeds;
    }

    /**
     * @return list<array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }>
     */
    private function parseRowsSections(SubmissionFormInstance $instance): array
    {
        $lines = [];
        $rowsByIndex = [];

        foreach ($this->schemaHelper->uniqueSections($instance->submissionForm) as $section) {
            if (! SubmissionFormSchemaHelper::sectionUsesSampleCards($section)) {
                continue;
            }

            // Merge every holder into one cell map per row index first so split
            // holders cannot emit duplicate sample lines.
            foreach ($section->elementHolders as $holder) {
                foreach ($this->groupHolderRows($instance, $holder->elements) as $rowIndex => $cells) {
                    $index = (int) $rowIndex;
                    if (! isset($rowsByIndex[$index])) {
                        $rowsByIndex[$index] = [];
                    }
                    $rowsByIndex[$index] = array_merge($rowsByIndex[$index], $cells);
                }
            }
        }

        ksort($rowsByIndex);

        $anchorCount = $this->countAnchorRowsFromInstance($instance);
        if ($anchorCount > 0) {
            $rowsByIndex = array_filter(
                $rowsByIndex,
                static fn (array $cells, int|string $index): bool => (int) $index < $anchorCount,
                ARRAY_FILTER_USE_BOTH,
            );
        }

        foreach ($rowsByIndex as $rowIndex => $cells) {
            $line = $this->mapRowCells((int) $rowIndex, $cells);
            if ($this->rowHasAnalysisData($line) || $this->trfRowHasDisplayData($line)) {
                $line['number_of_samples'] = 1;
                $lines[] = $line;
            }
        }

        usort($lines, fn (array $a, array $b): int => $a['row_index'] <=> $b['row_index']);

        return $lines;
    }

    /**
     * Prefer physical sample anchors over flat multi-select array indexes.
     */
    private function countAnchorRowsFromInstance(SubmissionFormInstance $instance): int
    {
        $anchorNames = SubmissionFormSchemaHelper::sampleRowAnchorFieldNames();
        $count = 0;

        foreach ($instance->values as $value) {
            $name = (string) ($value->element?->name ?? '');
            if ($name === '' || ! in_array($name, $anchorNames, true)) {
                continue;
            }
            if ($value->array_index === null) {
                continue;
            }
            $count = max($count, ((int) $value->array_index) + 1);
        }

        return $count;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function linesFromSampleRowsFormData(SubmissionFormInstance $instance): array
    {
        $formData = $this->valueNormalizer->valuesMapFromInstance($instance);
        $sampleRows = $formData['sample_rows'] ?? [];

        if (! is_array($sampleRows) || $sampleRows === []) {
            return [];
        }

        $defaultSampleTypeId = $this->resolveHeaderSampleTypeId($instance);
        $defaultSampleTypeName = $this->resolveSampleTypeName($defaultSampleTypeId);
        $lines = [];

        foreach (array_values($sampleRows) as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            $line = $this->mapTrfSampleRow(
                (int) $rowIndex,
                $row,
                $defaultSampleTypeId,
                $defaultSampleTypeName,
            );

            if ($this->trfRowHasDisplayData($line)) {
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function deduplicateLines(array $lines): array
    {
        if ($lines === []) {
            return [];
        }

        $seen = [];
        $unique = [];

        foreach ($lines as $line) {
            $fingerprint = implode('|', [
                (string) ($line['analysis_type_id'] ?? ''),
                (string) ($line['analysis_element_id'] ?? ''),
                mb_strtolower(trim((string) ($line['parameter_label'] ?? ''))),
                mb_strtolower(trim((string) ($line['sample_description'] ?? ''))),
                mb_strtolower(trim((string) ($line['customer_sample_id'] ?? ''))),
            ]);

            if (isset($seen[$fingerprint])) {
                continue;
            }

            $seen[$fingerprint] = true;
            $unique[] = $line;
        }

        foreach ($unique as $index => &$line) {
            if (! array_key_exists('row_index', $line)) {
                $line['row_index'] = $index;
            }
        }
        unset($line);

        return $unique;
    }

    /**
     * @param  iterable<int, SubmissionFormElement>  $elements
     * @return array<int, array<string, array{element: SubmissionFormElement, value: string, display_value: string, file_path: ?string}>>
     */
    private function groupHolderRows(SubmissionFormInstance $instance, iterable $elements): array
    {
        $rowsData = [];

        foreach ($elements as $element) {
            $elementValues = $instance->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            foreach ($elementValues as $value) {
                $arrayIndex = (int) ($value->array_index ?? 0);

                if (! isset($rowsData[$arrayIndex])) {
                    $rowsData[$arrayIndex] = [];
                }

                $rowsData[$arrayIndex][$element->id] = [
                    'element' => $element,
                    'value' => (string) ($value->value ?? ''),
                    'display_value' => (string) ($value->value ?? ''),
                    'file_path' => $value->file_path,
                ];
            }
        }

        return $rowsData;
    }

    /**
     * @param  array<string, array{element: SubmissionFormElement, value: string, display_value: string, file_path: ?string}>  $cells
     * @return array<string, mixed>
     */
    private function mapRowCells(int $rowIndex, array $cells): array
    {
        $line = [
            'row_index' => $rowIndex,
            'customer_sample_id' => null,
            'sample_description' => null,
            'parameter_category' => null,
            'sample_type_id' => null,
            'sample_type_name' => null,
            'analysis_type_id' => null,
            'analysis_type_name' => null,
            'analysis_element_id' => null,
            'parameter_label' => null,
            'number_of_samples' => null,
            'sample_quantity' => null,
            'sample_quantity_unit' => null,
            'sample_condition' => null,
            'state_of_sample' => null,
            'sampling_point' => null,
            'location' => null,
            'production_date' => null,
            'expiration_date' => null,
            'batch_number' => null,
            'picture_of_samples' => null,
            'attributes' => [],
        ];

        foreach ($cells as $cell) {
            $element = $cell['element'];
            $rawValue = $this->normalizeCellText($cell['value']);
            $display = $this->normalizeCellText($cell['display_value']);
            $filePath = $cell['file_path'] ?? null;

            if ($rawValue === '' && $display === '' && $filePath === null) {
                continue;
            }

            $type = (string) $element->element_type;
            $name = Str::lower((string) $element->name);
            $mapping = Str::lower((string) ($element->mapping_field ?? ''));

            $mapped = true;

            if ($type === 'sample_type_select') {
                $this->applySampleType($line, $rawValue, $display);
            } elseif ($type === 'analysis_type_select') {
                $this->applyAnalysisType($line, $rawValue, $display);
            } elseif ($type === 'analysis_elements_select') {
                $this->applyAnalysisElement($line, $rawValue, $display);
            } elseif ($name === 'sample_type_id') {
                $this->applySampleType($line, $rawValue, $display);
            } elseif ($name === 'analysis_type_id') {
                $this->applyAnalysisType($line, $rawValue, $display);
            } elseif ($name === 'sample_description') {
                $line['sample_description'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'parameter_category') {
                $line['parameter_category'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'lims_sample_no') {
                $line['customer_sample_id'] = $display !== '' ? $display : $rawValue;
            } elseif ($this->isCustomerSampleIdField($name, $mapping)) {
                $line['customer_sample_id'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'number_of_samples') {
                // Legacy "Qty" field name — mass/volume, not a sample count.
                if (($line['sample_quantity'] ?? null) === null || $line['sample_quantity'] === '') {
                    $line['sample_quantity'] = $display !== '' ? $display : $rawValue;
                }
                $line['number_of_samples'] = 1;
            } elseif ($name === 'sample_quantity') {
                $line['sample_quantity'] = $display !== '' ? $display : $rawValue;
                $line['number_of_samples'] = 1;
            } elseif ($name === 'sample_quantity_unit') {
                $line['sample_quantity_unit'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'test_category') {
                $categories = SubmissionFormSchemaHelper::testCategoryTokens($display !== '' ? $display : $rawValue);
                if ($categories === []) {
                    $categories = SubmissionFormSchemaHelper::testCategoryTokens(
                        $this->recoverLegacyCheckboxSelection($element, $rawValue),
                    );
                }
                if ($categories !== []) {
                    $category = implode(',', $categories);
                    $line['parameter_category'] = $category;
                    if (! isset($line['attributes'])) {
                        $line['attributes'] = [];
                    }
                    $line['attributes']['test_category'] = $category;
                }
            } elseif ($name === 'test_requirements') {
                $decoded = json_decode($rawValue, true);
                if (! is_array($decoded)) {
                    $selected = SubmissionFormSchemaHelper::testCategoryTokens($rawValue);
                    if ($selected === []) {
                        $selected = SubmissionFormSchemaHelper::testCategoryTokens(
                            $this->recoverLegacyCheckboxSelection($element, $rawValue),
                        );
                    }
                    if ($selected !== []) {
                        $decoded = [
                            'microbiology' => in_array('microbiology', $selected, true),
                            'legionella' => in_array('legionella', $selected, true),
                            'chemistry' => in_array('chemistry', $selected, true),
                        ];
                    }
                }
                if (is_array($decoded)) {
                    if (! isset($line['attributes'])) {
                        $line['attributes'] = [];
                    }
                    $line['attributes']['test_requirements'] = $decoded;
                }
            } elseif ($name === 'sample_condition') {
                $line['sample_condition'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'food_sample_type') {
                $line['attributes']['food_sample_type'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'tests_requested') {
                $line['attributes']['tests_requested'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'state_of_sample') {
                $line['state_of_sample'] = $display !== '' ? $display : $rawValue;
            } elseif (in_array($name, ['sampling_point', 'sampling_location'], true)
                || in_array($type, ['sample_point_select', 'customer_sample_point_select'], true)) {
                $line['sampling_point'] = $this->resolveSamplingPointLabel($display !== '' ? $display : $rawValue);
            } elseif ($name === 'location') {
                $line['location'] = $this->resolveSamplingPointLabel($display !== '' ? $display : $rawValue);
            } elseif ($name === 'production_date') {
                $line['production_date'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'expiration_date') {
                $line['expiration_date'] = $display !== '' ? $display : $rawValue;
            } elseif ($name === 'batch_number') {
                $line['batch_number'] = $display !== '' ? $display : $rawValue;
            } elseif (in_array($name, ['picture_of_samples', 'picture_of_sample'], true)) {
                $line['picture_of_samples'] = $filePath ?? ($display !== '' ? $display : $rawValue);
            } else {
                $mapped = false;
            }

            if (! $mapped) {
                $this->applyAttributeField($line, (string) $element->name, $display !== '' ? $display : $rawValue);
            }
        }

        if (($line['attributes'] ?? null) === []) {
            unset($line['attributes']);
        }

        try {
            $prefixRow = array_merge(
                is_array($line['attributes'] ?? null) ? $line['attributes'] : [],
                ['test_category' => $line['parameter_category'] ?? null],
            );
            $line['sample_code_prefix'] = app(JobSampleNumberingService::class)->resolveCategoryPrefixFromRow($prefixRow);
        } catch (\InvalidArgumentException) {
            $line['sample_code_prefix'] = null;
        }

        if (trim((string) ($line['parameter_label'] ?? '')) === '') {
            $tests = [];
            $requirements = is_array($line['attributes']['test_requirements'] ?? null)
                ? $line['attributes']['test_requirements']
                : [];

            foreach (['microbiology' => 'Microbiology', 'legionella' => 'Legionella', 'chemistry' => 'Chemistry'] as $key => $label) {
                if (! empty($requirements[$key])) {
                    $tests[] = $label;
                }
            }

            $categoryLabel = SubmissionFormSchemaHelper::testCategoryLabel($line['parameter_category'] ?? '');
            if ($tests === [] && $categoryLabel !== '') {
                $tests[] = $categoryLabel;
            }

            if ($tests !== []) {
                $line['parameter_label'] = implode(', ', $tests);
            }
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function applyAttributeField(array &$line, string $fieldName, string $value): void
    {
        if ($value === '') {
            return;
        }

        if (! isset($line['attributes'])) {
            $line['attributes'] = [];
        }

        $line['attributes'][$fieldName] = $value;

        $lowerName = Str::lower($fieldName);
        if (Str::startsWith($lowerName, 'sample_condition_')) {
            $label = Str::title(str_replace('_', ' ', Str::after($lowerName, 'sample_condition_')));
            $existing = (string) ($line['sample_condition'] ?? '');
            $line['sample_condition'] = trim($existing === '' ? $label : $existing.', '.$label);
        }
    }

    private function resolveNumericValue(string $rawValue, string $display): ?int
    {
        $candidate = $rawValue !== '' ? $rawValue : $display;
        if ($candidate === '' || ! is_numeric($candidate)) {
            return null;
        }

        return max(1, (int) $candidate);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function rowHasAnalysisData(array $line): bool
    {
        if (! empty($line['analysis_type_id']) || ! empty($line['analysis_element_id'])) {
            return true;
        }

        $sampleDescription = $this->normalizeCellText($line['sample_description'] ?? null);
        if ($sampleDescription !== '') {
            return true;
        }

        $parameterLabel = $this->normalizeCellText($line['parameter_label'] ?? null);

        return $parameterLabel !== '';
    }

    private function isCustomerSampleIdField(string $name, string $mapping): bool
    {
        foreach (['customer_sample_id', 'client_sample_id', 'sample_id', 'customer_sample'] as $needle) {
            if (Str::contains($name, $needle) || Str::contains($mapping, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Multi-pickers store their selections flat (CSV) on a single value row.
     *
     * @return list<string>
     */
    private function idTokens(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        $candidates = is_array($value)
            ? $value
            : (preg_split('/[,;|\r\n]+/', (string) $value) ?: []);

        $tokens = [];

        foreach ($candidates as $candidate) {
            if (is_array($candidate)) {
                foreach ($this->idTokens($candidate) as $nested) {
                    if (! in_array($nested, $tokens, true)) {
                        $tokens[] = $nested;
                    }
                }

                continue;
            }

            $token = trim((string) $candidate);
            if ($token === '' || in_array($token, $tokens, true)) {
                continue;
            }

            $tokens[] = $token;
        }

        return $tokens;
    }

    /**
     * @return array{ids: list<string>, names: list<string>}
     */
    private function resolveSampleTypeTokens(mixed $value, bool $uuidTokensOnly = false): array
    {
        $ids = [];
        $names = [];

        foreach ($this->idTokens($value) as $token) {
            if (Str::isUuid($token)) {
                $name = $this->resolveSampleTypeName($token);
                if ($name === null || $name === '') {
                    continue;
                }

                $ids[] = $token;
                $names[] = $name;

                continue;
            }

            if ($uuidTokensOnly) {
                continue;
            }

            $sampleType = SampleType::query()->where('name', $token)->first();
            if ($sampleType === null) {
                continue;
            }

            $ids[] = (string) $sampleType->id;
            $names[] = (string) $sampleType->name;
        }

        return [
            'ids' => array_values(array_unique($ids)),
            'names' => array_values(array_unique($names)),
        ];
    }

    private function firstFoodSampleTypeLabel(mixed $value): ?string
    {
        $foodTypeResolver = app(TrfDocumentCodeForSampleType::class);

        foreach ($this->idTokens($value) as $token) {
            if ($foodTypeResolver->isFoodSampleTypeLabel($token)) {
                return $token;
            }
        }

        return null;
    }

    /**
     * @return array{ids: list<string>, names: list<string>, food_labels: list<string>}
     */
    private function resolveAnalysisTypeTokens(mixed $value): array
    {
        $foodTypeResolver = app(TrfDocumentCodeForSampleType::class);
        $ids = [];
        $names = [];
        $foodLabels = [];

        foreach ($this->idTokens($value) as $token) {
            if ($foodTypeResolver->isFoodSampleTypeLabel($token)) {
                $foodLabels[] = $token;

                continue;
            }

            if (Str::isUuid($token)) {
                $name = $this->resolveAnalysisTypeName($token);
                if ($name === null || $name === '') {
                    continue;
                }

                $ids[] = $token;
                $names[] = $name;

                continue;
            }

            $analysisType = AnalysisType::query()->where('name', $token)->first();
            if ($analysisType === null) {
                continue;
            }

            $ids[] = (string) $analysisType->id;
            $names[] = (string) $analysisType->name;
        }

        return [
            'ids' => array_values(array_unique($ids)),
            'names' => array_values(array_unique($names)),
            'food_labels' => array_values(array_unique($foodLabels)),
        ];
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  list<string>  $ids
     * @param  list<string>  $names
     */
    private function assignSampleTypes(array &$line, array $ids, array $names): void
    {
        if ($ids === []) {
            return;
        }

        $line['sample_type_id'] = $ids[0];
        $line['sample_type_name'] = $names !== []
            ? implode(', ', $names)
            : $this->resolveSampleTypeName($ids[0]);

        if (count($ids) > 1) {
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['sample_type_ids'] = $ids;
            $line['attributes']['sample_type_names'] = $names;
        }
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  list<string>  $ids
     * @param  list<string>  $names
     */
    private function assignAnalysisTypes(array &$line, array $ids, array $names): void
    {
        if ($ids === []) {
            return;
        }

        $line['analysis_type_id'] = $ids[0];
        $line['analysis_type_name'] = $names !== []
            ? implode(', ', $names)
            : $this->resolveAnalysisTypeName($ids[0]);

        if (count($ids) > 1) {
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['analysis_type_ids'] = $ids;
            $line['attributes']['analysis_type_names'] = $names;
        }

        $this->fillSampleTypesFromAnalysisTypes($line, $ids);
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  list<string>  $analysisTypeIds
     */
    private function fillSampleTypesFromAnalysisTypes(array &$line, array $analysisTypeIds): void
    {
        if (trim((string) ($line['sample_type_id'] ?? '')) !== '') {
            return;
        }

        $ids = [];
        $names = [];

        foreach (AnalysisType::query()->whereKey($analysisTypeIds)->get() as $analysisType) {
            $sampleTypeId = trim((string) ($analysisType->sample_type_id ?? ''));
            if ($sampleTypeId === '' || in_array($sampleTypeId, $ids, true)) {
                continue;
            }

            $name = $this->resolveSampleTypeName($sampleTypeId);
            if ($name === null || $name === '') {
                continue;
            }

            $ids[] = $sampleTypeId;
            $names[] = $name;
        }

        $this->assignSampleTypes($line, $ids, $names);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function applySampleType(array &$line, string $rawValue, string $display): void
    {
        $resolved = $this->resolveSampleTypeTokens($rawValue !== '' ? $rawValue : $display);

        if ($resolved['ids'] !== []) {
            $this->assignSampleTypes($line, $resolved['ids'], $resolved['names']);

            return;
        }

        // A name containing commas survives here because the whole value is matched.
        $byName = SampleType::query()->where('name', trim($rawValue !== '' ? $rawValue : $display))->first();
        if ($byName !== null) {
            $this->assignSampleTypes($line, [(string) $byName->id], [(string) $byName->name]);

            return;
        }

        $line['sample_type_id'] = $rawValue !== '' ? $rawValue : null;
        $line['sample_type_name'] = $display !== '' ? $display : $this->resolveSampleTypeName($line['sample_type_id']);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function applyAnalysisType(array &$line, string $rawValue, string $display): void
    {
        $foodTypeResolver = app(TrfDocumentCodeForSampleType::class);
        if ($foodTypeResolver->isFoodSampleTypeLabel($rawValue)) {
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['food_sample_type'] = $rawValue;
            $line['analysis_type_name'] = $rawValue;
            $this->resolveFoodMatrixAnalysisTypeOnLine($line, $rawValue);

            return;
        }

        $candidate = $rawValue !== '' ? $rawValue : $display;
        $resolved = $this->resolveAnalysisTypeTokens($candidate);

        if ($resolved['ids'] === [] && $resolved['food_labels'] !== []) {
            $foodLabel = $resolved['food_labels'][0];
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['food_sample_type'] = $foodLabel;
            $line['analysis_type_name'] = $foodLabel;
            $this->resolveFoodMatrixAnalysisTypeOnLine($line, $foodLabel);

            return;
        }

        if ($resolved['ids'] !== []) {
            $this->assignAnalysisTypes($line, $resolved['ids'], $resolved['names']);
        } else {
            // Names containing commas survive here because the whole value is matched.
            $byName = AnalysisType::query()->where('name', trim($candidate))->first();

            if ($byName !== null) {
                $this->assignAnalysisTypes($line, [(string) $byName->id], [(string) $byName->name]);
            } else {
                $line['analysis_type_id'] = $rawValue !== '' ? $rawValue : null;
                $line['analysis_type_name'] = $display !== '' ? $display : $this->resolveAnalysisTypeName($line['analysis_type_id']);
            }
        }

        if ($line['analysis_type_name'] !== null
            && $foodTypeResolver->isFoodSampleTypeLabel((string) $line['analysis_type_name'])) {
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['food_sample_type'] = (string) $line['analysis_type_name'];
        }

        if ($line['sample_type_id'] === null
            && $line['analysis_type_id'] !== null
            && Str::isUuid((string) $line['analysis_type_id'])) {
            $analysisType = AnalysisType::query()->find($line['analysis_type_id']);
            if ($analysisType?->sample_type_id) {
                $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function applyAnalysisElement(array &$line, string $rawValue, string $display): void
    {
        $tokens = $this->extractTokens($rawValue);
        if ($tokens === []) {
            return;
        }

        $resolvedLabels = [];
        $resolvedElementIds = [];
        $elementAnalysisTypeIds = [];
        $hadExplicitAnalysisType = ($line['analysis_type_id'] ?? null) !== null;

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            $elementRecord = $this->resolveAnalysisElementToken($token, $line);

            if ($elementRecord) {
                $resolvedElementIds[] = (string) $elementRecord->id;
                $resolvedLabels[] = $elementRecord->analyte?->name ?? $token;

                $elementAnalysisTypeId = trim((string) ($elementRecord->analysis_type_id ?? ''));
                if ($elementAnalysisTypeId !== '' && ! in_array($elementAnalysisTypeId, $elementAnalysisTypeIds, true)) {
                    $elementAnalysisTypeIds[] = $elementAnalysisTypeId;
                }

                if ($line['analysis_type_id'] === null) {
                    $line['analysis_type_id'] = (string) $elementRecord->analysis_type_id;
                    $line['analysis_type_name'] = $this->resolveAnalysisTypeName($line['analysis_type_id']);
                }

                if ($line['sample_type_id'] === null) {
                    $analysisType = AnalysisType::query()->find($elementRecord->analysis_type_id);
                    if ($analysisType?->sample_type_id) {
                        $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                        $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
                    }
                }

                continue;
            }

            $resolvedLabels[] = $token;
        }

        if ($resolvedElementIds !== []) {
            $line['analysis_element_id'] = $resolvedElementIds[0];
            if (! isset($line['attributes']) || ! is_array($line['attributes'])) {
                $line['attributes'] = [];
            }
            $line['attributes']['analysis_element_ids'] = $resolvedElementIds;

            if (! $hadExplicitAnalysisType && count($elementAnalysisTypeIds) > 1) {
                $names = [];
                foreach ($elementAnalysisTypeIds as $analysisTypeId) {
                    $name = $this->resolveAnalysisTypeName($analysisTypeId);
                    if ($name !== null && $name !== '') {
                        $names[] = $name;
                    }
                }

                $this->assignAnalysisTypes($line, $elementAnalysisTypeIds, $names);
            }
        }

        if ($resolvedLabels !== []) {
            $line['parameter_label'] = $display !== '' ? $display : implode(', ', $resolvedLabels);

            return;
        }

        $firstToken = $tokens[0];

        $analysisType = null;
        if (Str::isUuid($firstToken)) {
            $analysisType = AnalysisType::query()->find($firstToken);
        }
        if (! $analysisType) {
            $analysisType = AnalysisType::query()->where('name', $firstToken)->first();
        }

        if ($analysisType) {
            $line['analysis_type_id'] = (string) $analysisType->id;
            $line['analysis_type_name'] = $analysisType->name;
            $line['parameter_label'] = $display !== '' ? $display : $analysisType->name;
            if ($analysisType->sample_type_id) {
                $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
            }
        } else {
            $line['parameter_label'] = $display !== '' ? $display : $firstToken;
        }
    }

    /**
     * @return list<string>
     */
    private function extractTokens(string $rawValue): array
    {
        if ($rawValue === '') {
            return [];
        }

        if (str_starts_with($rawValue, '[')) {
            $decoded = json_decode($rawValue, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map('strval', $decoded)));
            }
        }

        if (str_contains($rawValue, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $rawValue))));
        }

        return [$rawValue];
    }

    /**
     * @return list<array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }>
     */
    private function fallbackLinesFromHeader(SubmissionFormInstance $instance): array
    {
        $sampleTypeId = $this->resolveHeaderSampleTypeId($instance);
        $sampleTypeName = $this->resolveSampleTypeName($sampleTypeId);

        $canonicalElementIds = $this->schemaHelper
            ->uniqueElements($instance->submissionForm)
            ->pluck('id')
            ->flip();

        $parameters = $instance->values
            ->filter(function ($value) use ($canonicalElementIds) {
                $element = $value->element;
                if (! $element) {
                    return false;
                }

                if (! isset($canonicalElementIds[$element->id])) {
                    return false;
                }

                $elementType = (string) ($element->element_type ?? '');
                $mappingField = (string) ($element->mapping_field ?? '');
                $elementName = Str::lower(trim((string) $element->name.' '.(string) $element->label));

                return $elementType === 'analysis_elements_select'
                    || in_array($mappingField, ['analysis_element_id', 'analyte_id'], true)
                    || Str::contains($elementName, ['test required', 'tests required', 'parameter', 'analysis']);
            });

        $lines = [];
        $rowIndex = 0;

        foreach ($parameters as $value) {
            $tokens = $this->extractTokens((string) $value->value);
            foreach ($tokens as $token) {
                $token = trim($token);
                if ($token === '') {
                    continue;
                }

                $line = [
                    'row_index' => $rowIndex++,
                    'customer_sample_id' => null,
                    'sample_type_id' => $sampleTypeId,
                    'sample_type_name' => $sampleTypeName,
                    'analysis_type_id' => null,
                    'analysis_type_name' => null,
                    'analysis_element_id' => null,
                    'parameter_label' => null,
                ];

                $elementRecord = null;
                
                // First try to find by UUID
                if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $token)) {
                    $elementRecord = AnalysisElements::query()->with('analyte')->find($token);
                }
                
                // If not found by UUID, try to find by analyte name
                if (!$elementRecord) {
                    $elementRecord = AnalysisElements::query()->with('analyte')
                        ->whereHas('analyte', function ($query) use ($token) {
                            $query->where('name', $token);
                        })
                        ->first();
                }
                
                if ($elementRecord) {
                    $line['analysis_element_id'] = (string) $elementRecord->id;
                    $line['analysis_type_id'] = (string) $elementRecord->analysis_type_id;
                    $line['analysis_type_name'] = $this->resolveAnalysisTypeName($line['analysis_type_id']);
                    $line['parameter_label'] = $elementRecord->analyte?->name ?? 'Parameter';
                    $analysisType = AnalysisType::query()->find($elementRecord->analysis_type_id);
                    if ($analysisType?->sample_type_id) {
                        $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                        $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
                    }
                } else {
                    $analysisType = null;
                    
                    // First try to find by UUID
                    if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $token)) {
                        $analysisType = AnalysisType::query()->find($token);
                    }
                    
                    // If not found by UUID, try to find by name
                    if (!$analysisType) {
                        $analysisType = AnalysisType::query()->where('name', $token)->first();
                    }
                    
                    if ($analysisType) {
                        $line['analysis_type_id'] = (string) $analysisType->id;
                        $line['analysis_type_name'] = $analysisType->name;
                        if ($analysisType->sample_type_id) {
                            $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                            $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
                        }
                    } else {
                        $line['parameter_label'] = $instance->resolveDisplayValue($value->element, $token);
                    }
                }

                if ($this->rowHasAnalysisData($line)) {
                    $lines[] = $line;
                }
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function linesHaveRichSampleDetail(array $lines): bool
    {
        foreach ($lines as $line) {
            if (trim((string) ($line['sample_description'] ?? '')) !== '') {
                return true;
            }

            if (trim((string) ($line['parameter_label'] ?? '')) !== '') {
                return true;
            }

            if (! empty($line['analysis_element_id']) || ! empty($line['analysis_type_id'])) {
                return true;
            }
        }

        return false;
    }

    private function resolveHeaderSampleTypeId(SubmissionFormInstance $instance): ?string
    {
        $selectedSampleTypeId = $instance->resolveSelectedSampleTypeId();
        if ($selectedSampleTypeId !== null) {
            return $selectedSampleTypeId;
        }

        foreach ($instance->values as $value) {
            $element = $value->element;
            if ($element && $element->element_type === 'sample_type_select' && $value->value) {
                $resolved = $this->resolveSampleTypeTokens($value->value);
                if ($resolved['ids'] !== []) {
                    return $resolved['ids'][0];
                }
            }
        }

        $formTypeId = $instance->submissionForm->sampleTypes->first()?->id;

        return $formTypeId ? (string) $formTypeId : null;
    }

    private function resolveSampleTypeName(?string $sampleTypeId): ?string
    {
        if ($sampleTypeId === null || $sampleTypeId === '' || ! Str::isUuid($sampleTypeId)) {
            return null;
        }

        return SampleType::query()->whereKey($sampleTypeId)->value('name');
    }

    private function resolveAnalysisTypeName(?string $analysisTypeId): ?string
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return null;
        }

        if (app(TrfDocumentCodeForSampleType::class)->isFoodSampleTypeLabel($analysisTypeId)) {
            return $analysisTypeId;
        }

        if (! Str::isUuid($analysisTypeId)) {
            return null;
        }

        return AnalysisType::query()->whereKey($analysisTypeId)->value('name');
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function mapTrfSampleRow(
        int $rowIndex,
        array $row,
        ?string $defaultSampleTypeId,
        ?string $defaultSampleTypeName,
    ): array {
        $quantityData = $this->resolveSampleQuantityFromRow($row);

        // A card carries its own sample type on unlinked TRFs; the header default is only a fallback.
        $rowSampleTypes = $this->resolveSampleTypeTokens($row['sample_type_id'] ?? null);
        if ($rowSampleTypes['ids'] === []) {
            $rowSampleTypes = $this->resolveSampleTypeTokens($row['sample_type'] ?? null, uuidTokensOnly: true);
        }

        $line = [
            'row_index' => $rowIndex,
            'customer_sample_id' => $this->nullableString($row['sample_no'] ?? $row['lims_sample_no'] ?? null),
            'sample_description' => $this->nullableString($row['sample_description'] ?? null),
            'parameter_category' => null,
            'sample_type_id' => $rowSampleTypes['ids'][0] ?? $defaultSampleTypeId,
            'sample_type_name' => $rowSampleTypes['names'] !== []
                ? implode(', ', $rowSampleTypes['names'])
                : $defaultSampleTypeName,
            'analysis_type_id' => null,
            'analysis_type_name' => null,
            'analysis_element_id' => null,
            'parameter_label' => null,
            'number_of_samples' => 1,
            'sample_quantity' => $quantityData['sample_quantity'],
            'sample_quantity_unit' => $quantityData['sample_quantity_unit'],
            'sample_condition' => $this->nullableString($row['sample_condition'] ?? null),
            'state_of_sample' => $this->nullableString($row['state_of_sample'] ?? null),
            'sampling_point' => $this->resolveSamplingPointLabel(
                $this->nullableString($row['sampling_point'] ?? $row['sampling_location'] ?? null)
            ),
            'location' => $this->resolveSamplingPointLabel(
                $this->nullableString($row['location'] ?? null)
            ),
            'production_date' => $this->nullableString($row['production_date'] ?? null),
            'expiration_date' => $this->nullableString($row['expiration_date'] ?? null),
            'batch_number' => $this->nullableString($row['batch_number'] ?? null),
            'picture_of_samples' => null,
            'attributes' => [],
        ];

        if (count($rowSampleTypes['ids']) > 1) {
            $line['attributes']['sample_type_ids'] = $rowSampleTypes['ids'];
            $line['attributes']['sample_type_names'] = $rowSampleTypes['names'];
        }

        $foodSampleType = $this->firstFoodSampleTypeLabel($row['sample_type'] ?? null);
        if ($foodSampleType === null) {
            $foodSampleType = $this->firstFoodSampleTypeLabel($row['analysis_type_id'] ?? null);
        }

        $rowSampleTypeName = $rowSampleTypes['names'] !== []
            ? implode(', ', $rowSampleTypes['names'])
            : $defaultSampleTypeName;

        $this->applyRowAnalysisSelection(
            $line,
            $row,
            $rowSampleTypes['ids'][0] ?? $defaultSampleTypeId,
            $foodSampleType,
        );

        if ($foodSampleType !== null
            && empty($line['attributes']['food_sample_type'])) {
            $line['attributes']['food_sample_type'] = $foodSampleType;
            if ($rowSampleTypeName !== null && $rowSampleTypeName !== '') {
                $line['sample_type_name'] = $rowSampleTypeName.' — '.$foodSampleType;
            } else {
                $line['sample_type_name'] = $foodSampleType;
            }
        } elseif (($line['attributes']['food_sample_type'] ?? null) !== null
            && $rowSampleTypeName !== null
            && $rowSampleTypeName !== ''
            && ! str_contains((string) ($line['sample_type_name'] ?? ''), '—')) {
            $line['sample_type_name'] = $rowSampleTypeName.' — '.$line['attributes']['food_sample_type'];
        }

        $tests = [];
        $parameterTokens = $this->referenceLabelResolver->extractTokens($row['parameters'] ?? null);
        if ($parameterTokens !== []) {
            $resolvedParameters = $this->referenceLabelResolver->resolveMixed($parameterTokens);
            if ($resolvedParameters !== '') {
                $tests[] = $resolvedParameters;
            }

            $this->applyParameterTokensToLine($line, $parameterTokens);
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['parameters'] = is_array($row['parameters'] ?? null)
                ? implode(',', $parameterTokens)
                : trim((string) ($row['parameters'] ?? implode(',', $parameterTokens)));
        } else {
            foreach (['microbiology' => 'Microbiology', 'legionella' => 'Legionella', 'chemistry' => 'Chemistry', 'chemical_analysis' => 'Chemistry'] as $key => $label) {
                if (! empty($row[$key])) {
                    $tests[] = $label;
                }
            }

            $category = $this->rowTestCategory($row);
            if ($category !== '') {
                $line['parameter_category'] = $category;
            }

            if ($tests === [] && $category !== '') {
                $tests[] = SubmissionFormSchemaHelper::testCategoryLabel($category);
            }
        }

        $category = $this->rowTestCategory($row);
        if ($category !== '' && ! isset($line['parameter_category'])) {
            $line['parameter_category'] = $category;
        }

        try {
            $line['sample_code_prefix'] = app(JobSampleNumberingService::class)->resolveCategoryPrefixFromRow($row);
        } catch (\InvalidArgumentException) {
            $line['sample_code_prefix'] = null;
        }

        if ($tests !== []) {
            $line['parameter_label'] = implode(', ', array_values(array_filter(
                $tests,
                fn (string $test): bool => ! $this->isTestCategoryLabel($test),
            )));
        }

        if ($line['attributes'] === []) {
            unset($line['attributes']);
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{sample_quantity: ?string, sample_quantity_unit: ?string}
     */
    private function resolveSampleQuantityFromRow(array $row): array
    {
        $quantity = $this->nullableString($row['sample_quantity'] ?? null);
        $unit = $this->nullableString($row['sample_quantity_unit'] ?? null);

        if ($quantity === null && isset($row['number_of_samples']) && $row['number_of_samples'] !== '') {
            // Legacy TRF "Qty" column name — treat as quantity, never as sample count.
            $quantity = $this->nullableString($row['number_of_samples']);
        }

        if ($quantity === null && isset($row['qty']) && $row['qty'] !== '') {
            $legacyQty = trim((string) $row['qty']);
            if (preg_match('/^([\d.]+)\s*(.*)$/u', $legacyQty, $matches)) {
                $quantity = $matches[1];
                $unit = trim($matches[2]) !== '' ? trim($matches[2]) : $unit;
            } else {
                $quantity = $legacyQty;
            }
        }

        return [
            'sample_quantity' => $quantity,
            'sample_quantity_unit' => $unit,
        ];
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function trfRowHasDisplayData(array $line): bool
    {
        if ($this->rowHasAnalysisData($line)) {
            return true;
        }

        return trim((string) ($line['sample_description'] ?? '')) !== ''
            || trim((string) ($line['parameter_label'] ?? '')) !== '';
    }

    private function nullableString(mixed $value): ?string
    {
        $string = $this->normalizeCellText($value);

        return $string !== '' ? $string : null;
    }

    /**
     * Resolve sample-point UUID references to their display names.
     */
    private function resolveSamplingPointLabel(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! Str::isUuid($value)) {
            return $value;
        }

        $name = SamplePoint::query()->whereKey($value)->value('name');

        return $name !== null && $name !== '' ? (string) $name : $value;
    }

    private function normalizeCellText(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '') {
            return '';
        }

        $normalized = strtolower(str_replace([' ', '_'], '', $text));
        if (in_array($normalized, ['n/a', 'na', '-', '—', 'null', 'none', 'notapplicable'], true)) {
            return '';
        }

        return $text;
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  list<string>  $tokens
     */
    private function applyParameterTokensToLine(array &$line, array $tokens): void
    {
        $elementIds = [];
        $resolvedLabels = [];
        $pinnedSampleTypeId = $line['sample_type_id'] ?? null;
        $pinnedSampleTypeName = trim((string) ($line['sample_type_name'] ?? ''));

        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '') {
                continue;
            }

            $elementRecord = $this->resolveAnalysisElementToken($token, $line);

            if ($elementRecord) {
                $elementIds[] = (string) $elementRecord->id;
                $resolvedLabels[] = $elementRecord->analyte?->name ?? $token;

                if ($line['analysis_type_id'] === null) {
                    $line['analysis_type_id'] = (string) $elementRecord->analysis_type_id;
                    $line['analysis_type_name'] = $this->resolveAnalysisTypeName($line['analysis_type_id']);
                }

                continue;
            }

            if (Str::isUuid($token)) {
                $analysisType = AnalysisType::query()->find($token);
                if ($analysisType && $line['analysis_type_id'] === null) {
                    $line['analysis_type_id'] = (string) $analysisType->id;
                    $line['analysis_type_name'] = $analysisType->name;
                }
            } else {
                $resolvedLabels[] = $token;
            }
        }

        if ($pinnedSampleTypeId !== null) {
            $line['sample_type_id'] = $pinnedSampleTypeId;
            $line['sample_type_name'] = $pinnedSampleTypeName !== ''
                ? $pinnedSampleTypeName
                : $this->resolveSampleTypeName($pinnedSampleTypeId);
        }

        if ($elementIds === []) {
            return;
        }

        $line['analysis_element_id'] = $elementIds[0];
        if (! isset($line['attributes']) || ! is_array($line['attributes'])) {
            $line['attributes'] = [];
        }
        $line['attributes']['analysis_element_ids'] = $elementIds;

        if (trim((string) ($line['parameter_label'] ?? '')) === '' && $resolvedLabels !== []) {
            $line['parameter_label'] = implode(', ', $resolvedLabels);
        }
    }

    /**
     * @param  array<string, mixed>  $line
     * @param  array<string, mixed>  $row
     */
    private function applyRowAnalysisSelection(
        array &$line,
        array $row,
        ?string $defaultSampleTypeId,
        ?string $foodSampleTypeLabel = null,
    ): void {
        $candidate = $this->nullableString($row['analysis_type_id'] ?? null);
        if ($candidate === null) {
            if ($foodSampleTypeLabel !== null) {
                $this->resolveFoodMatrixAnalysisTypeOnLine($line, $foodSampleTypeLabel, $defaultSampleTypeId);
            }

            return;
        }

        $resolved = $this->resolveAnalysisTypeTokens($candidate);

        if ($resolved['ids'] !== []) {
            $this->assignAnalysisTypes($line, $resolved['ids'], $resolved['names']);

            if ($resolved['food_labels'] !== []) {
                $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
                $line['attributes']['food_sample_type'] = $resolved['food_labels'][0];
            }

            return;
        }

        if ($resolved['food_labels'] !== []) {
            $foodLabel = $resolved['food_labels'][0];
            $line['attributes'] = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
            $line['attributes']['food_sample_type'] = $foodLabel;
            $line['analysis_type_name'] = $foodLabel;
            $this->resolveFoodMatrixAnalysisTypeOnLine($line, $foodLabel, $defaultSampleTypeId);

            return;
        }

        // Names containing commas survive here because the whole value is matched.
        $analysisType = AnalysisType::query()->where('name', $candidate)->first();
        if ($analysisType !== null) {
            $this->assignAnalysisTypes($line, [(string) $analysisType->id], [(string) $analysisType->name]);

            return;
        }

        if ($foodSampleTypeLabel !== null) {
            $this->resolveFoodMatrixAnalysisTypeOnLine($line, $foodSampleTypeLabel, $defaultSampleTypeId);
        }
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveFoodMatrixAnalysisTypeOnLine(
        array &$line,
        string $foodLabel,
        ?string $preferredSampleTypeId = null,
    ): void {
        $sampleTypeId = (string) ($line['sample_type_id'] ?? $preferredSampleTypeId ?? '');
        if ($sampleTypeId === '') {
            return;
        }

        $analysisType = AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->where('name', $foodLabel)
            ->first();

        if ($analysisType === null) {
            return;
        }

        $line['analysis_type_id'] = (string) $analysisType->id;
        $line['analysis_type_name'] = (string) $analysisType->name;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveAnalysisElementToken(string $token, array $line): ?AnalysisElements
    {
        if (Str::isUuid($token)) {
            return AnalysisElements::query()->with('analyte')->find($token);
        }

        $query = AnalysisElements::query()->with('analyte')
            ->whereHas('analyte', fn ($analyteQuery) => $analyteQuery->where('name', $token));

        $scopedAnalysisTypeIds = $this->scopedAnalysisTypeIdsForLine($line);
        if ($scopedAnalysisTypeIds !== []) {
            $query->whereIn('analysis_type_id', $scopedAnalysisTypeIds);
        }

        $element = $query->first();
        if ($element !== null) {
            return $element;
        }

        if ($scopedAnalysisTypeIds !== []) {
            return null;
        }

        return AnalysisElements::query()->with('analyte')
            ->whereHas('analyte', fn ($analyteQuery) => $analyteQuery->where('name', $token))
            ->first();
    }

    /**
     * A card can carry several analysis types, so parameters must resolve against all of them.
     *
     * @param  array<string, mixed>  $line
     * @return list<string>
     */
    private function scopedAnalysisTypeIdsForLine(array $line): array
    {
        $attributes = is_array($line['attributes'] ?? null) ? $line['attributes'] : [];
        $ids = [];

        foreach ($this->idTokens($attributes['analysis_type_ids'] ?? null) as $token) {
            if (Str::isUuid($token) && ! in_array($token, $ids, true)) {
                $ids[] = $token;
            }
        }

        $scoped = $this->scopedAnalysisTypeIdForLine($line);
        if ($scoped !== null && ! in_array($scoped, $ids, true)) {
            $ids[] = $scoped;
        }

        return $ids;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function scopedAnalysisTypeIdForLine(array $line): ?string
    {
        $analysisTypeId = trim((string) ($line['analysis_type_id'] ?? ''));
        if ($analysisTypeId !== '' && Str::isUuid($analysisTypeId)) {
            return $analysisTypeId;
        }

        $foodLabel = trim((string) ($line['attributes']['food_sample_type'] ?? ''));
        $sampleTypeId = trim((string) ($line['sample_type_id'] ?? ''));
        if ($foodLabel === '' || $sampleTypeId === '' || ! Str::isUuid($sampleTypeId)) {
            return null;
        }

        $resolved = AnalysisType::query()
            ->where('sample_type_id', $sampleTypeId)
            ->where('name', $foodLabel)
            ->value('id');

        return $resolved ? (string) $resolved : null;
    }

    private function isTestCategoryLabel(string $label): bool
    {
        foreach (preg_split('/\s*,\s*/', strtolower(trim($label))) ?: [] as $part) {
            if (! in_array($part, [
                'microbiology',
                'chemistry',
                'chemical',
                'chemical analysis',
                'legionella',
            ], true)) {
                return false;
            }
        }

        return trim($label) !== '';
    }

    /**
     * Rows saved before checkbox groups stored option keys hold the stringified
     * booleans of each option ("1," / "1,1"), so position still identifies the
     * selection. Map those positions back onto the element's options.
     */
    private function recoverLegacyCheckboxSelection(SubmissionFormElement $element, string $rawValue): string
    {
        if ($rawValue === '' || preg_match('/^[01]?(,[01]?)*$/', $rawValue) !== 1) {
            return '';
        }

        $options = $element->options ?? [];
        if (! is_array($options) || array_is_list($options) === false) {
            return '';
        }

        $selected = [];
        foreach (explode(',', $rawValue) as $position => $token) {
            $option = $options[$position] ?? null;
            if (trim($token) !== '1' || $option === null) {
                continue;
            }

            $value = trim((string) (is_array($option) ? ($option['value'] ?? $option['label'] ?? '') : $option));
            if ($value !== '') {
                $selected[] = $value;
            }
        }

        return implode(',', $selected);
    }

    /**
     * Canonical test-category slugs for a TRF row, tolerating multi-select CSVs.
     *
     * @param  array<string, mixed>  $row
     */
    private function rowTestCategory(array $row): string
    {
        return implode(',', SubmissionFormSchemaHelper::testCategoryTokens(
            $row['test_category'] ?? $row['parameter_category'] ?? null,
        ));
    }
}
