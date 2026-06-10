<?php

namespace App\Services\SubmissionForm;

use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SubmissionFormElement;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use Illuminate\Support\Str;

class SubmissionRequestSampleLineService
{
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

        $rowLines = $this->parseRowsSections($instance);

        if ($rowLines !== []) {
            return $rowLines;
        }

        return $this->fallbackLinesFromHeader($instance);
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
                'sample_type_id' => $line['sample_type_id'],
                'analysis_type_id' => (string) ($line['analysis_type_id'] ?? ''),
                'analysis_element_id' => $line['analysis_element_id'],
                'parameter_label' => (string) ($line['parameter_label'] ?? 'Parameter'),
                'customer_sample_id' => $line['customer_sample_id'],
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

        foreach ($instance->submissionForm->sections as $section) {
            if ($section->section_type !== 'rows_section') {
                continue;
            }

            foreach ($section->elementHolders as $holder) {
                $rowsData = $this->groupHolderRows($instance, $holder->elements);

                foreach ($rowsData as $rowIndex => $cells) {
                    $line = $this->mapRowCells((int) $rowIndex, $cells);
                    if ($this->rowHasAnalysisData($line)) {
                        $lines[] = $line;
                    }
                }
            }
        }

        usort($lines, fn (array $a, array $b): int => $a['row_index'] <=> $b['row_index']);

        return $lines;
    }

    /**
     * @param  iterable<int, SubmissionFormElement>  $elements
     * @return array<int, array<string, array{element: SubmissionFormElement, value: string, display_value: string}>>
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
                    'display_value' => (string) $instance->resolveDisplayValue($element, $value->value),
                ];
            }
        }

        return $rowsData;
    }

    /**
     * @param  array<string, array{element: SubmissionFormElement, value: string, display_value: string}>  $cells
     * @return array{
     *     row_index: int,
     *     customer_sample_id: ?string,
     *     sample_type_id: ?string,
     *     sample_type_name: ?string,
     *     analysis_type_id: ?string,
     *     analysis_type_name: ?string,
     *     analysis_element_id: ?string,
     *     parameter_label: ?string
     * }
     */
    private function mapRowCells(int $rowIndex, array $cells): array
    {
        $line = [
            'row_index' => $rowIndex,
            'customer_sample_id' => null,
            'sample_type_id' => null,
            'sample_type_name' => null,
            'analysis_type_id' => null,
            'analysis_type_name' => null,
            'analysis_element_id' => null,
            'parameter_label' => null,
        ];

        foreach ($cells as $cell) {
            $element = $cell['element'];
            $rawValue = trim($cell['value']);
            $display = trim($cell['display_value']);

            if ($rawValue === '' && $display === '') {
                continue;
            }

            $type = (string) $element->element_type;
            $name = Str::lower((string) $element->name);
            $mapping = Str::lower((string) ($element->mapping_field ?? ''));

            match (true) {
                $type === 'sample_type_select' => $this->applySampleType($line, $rawValue, $display),
                $type === 'analysis_type_select' => $this->applyAnalysisType($line, $rawValue, $display),
                $type === 'analysis_elements_select' => $this->applyAnalysisElement($line, $rawValue, $display),
                $this->isCustomerSampleIdField($name, $mapping) => $line['customer_sample_id'] = $display !== '' ? $display : $rawValue,
                default => null,
            };
        }

        return $line;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function rowHasAnalysisData(array $line): bool
    {
        return ! empty($line['analysis_type_id'])
            || ! empty($line['analysis_element_id']);
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
     * @param  array<string, mixed>  $line
     */
    private function applySampleType(array &$line, string $rawValue, string $display): void
    {
        $line['sample_type_id'] = $rawValue !== '' ? $rawValue : null;
        $line['sample_type_name'] = $display !== '' ? $display : $this->resolveSampleTypeName($line['sample_type_id']);
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function applyAnalysisType(array &$line, string $rawValue, string $display): void
    {
        $line['analysis_type_id'] = $rawValue !== '' ? $rawValue : null;
        $line['analysis_type_name'] = $display !== '' ? $display : $this->resolveAnalysisTypeName($line['analysis_type_id']);

        if ($line['sample_type_id'] === null && $line['analysis_type_id'] !== null) {
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
        $firstToken = $tokens[0] ?? '';

        if ($firstToken === '') {
            return;
        }

        $elementRecord = AnalysisElements::query()->with('analyte')->find($firstToken);
        if ($elementRecord) {
            $line['analysis_element_id'] = (string) $elementRecord->id;
            $line['parameter_label'] = $display !== '' ? $display : ($elementRecord->analyte?->name ?? 'Parameter');
            $line['analysis_type_id'] = (string) $elementRecord->analysis_type_id;
            $line['analysis_type_name'] = $this->resolveAnalysisTypeName($line['analysis_type_id']);

            $analysisType = AnalysisType::query()->find($elementRecord->analysis_type_id);
            if ($analysisType?->sample_type_id) {
                $line['sample_type_id'] = (string) $analysisType->sample_type_id;
                $line['sample_type_name'] = $this->resolveSampleTypeName($line['sample_type_id']);
            }

            return;
        }

        $line['analysis_element_id'] = $firstToken;
        $line['parameter_label'] = $display !== '' ? $display : $firstToken;
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

        $parameters = $instance->values
            ->filter(function ($value) {
                $element = $value->element;
                if (! $element) {
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

    private function resolveHeaderSampleTypeId(SubmissionFormInstance $instance): ?string
    {
        foreach ($instance->values as $value) {
            $element = $value->element;
            if ($element && $element->element_type === 'sample_type_select' && $value->value) {
                return (string) $value->value;
            }
        }

        $formTypeId = $instance->submissionForm->sampleTypes->first()?->id;

        return $formTypeId ? (string) $formTypeId : null;
    }

    private function resolveSampleTypeName(?string $sampleTypeId): ?string
    {
        if ($sampleTypeId === null || $sampleTypeId === '') {
            return null;
        }

        return SampleType::query()->whereKey($sampleTypeId)->value('name');
    }

    private function resolveAnalysisTypeName(?string $analysisTypeId): ?string
    {
        if ($analysisTypeId === null || $analysisTypeId === '') {
            return null;
        }

        return AnalysisType::query()->whereKey($analysisTypeId)->value('name');
    }
}
