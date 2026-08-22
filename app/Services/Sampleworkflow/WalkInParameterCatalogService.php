<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisType;
use App\SampleAnalysisStage;

class WalkInParameterCatalogService
{
    /**
     * @param  list<string>  $sampleTypeIds
     * @return list<array{
     *     analysis_type_id: string,
     *     analysis_type: string,
     *     sample_type: string,
     *     tests: list<array{
     *         id: string,
     *         name: string,
     *         analysis_type_id: string,
     *         analysis_type: string,
     *         lab_section_code: string,
     *         reporting_unit: string,
     *         lod: string,
     *         loq: string,
     *         mu: string,
     *         tat: string,
     *         method: string
     *     }>
     * }>
     */
    public function groupsForSampleTypes(array $sampleTypeIds): array
    {
        $sampleTypeIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $sampleTypeIds
        ), static fn (string $id): bool => $id !== '')));

        if ($sampleTypeIds === []) {
            return [];
        }

        $analysisTypes = AnalysisType::query()
            ->with('sample_type')
            ->whereIn('sample_type_id', $sampleTypeIds)
            ->where(function ($query): void {
                $query->where('active', true)->orWhere('active', 1);
            })
            ->orderBy('name')
            ->get();

        if ($analysisTypes->isEmpty()) {
            return [];
        }

        $elements = AnalysisElements::query()
            ->whereIn('analysis_type_id', $analysisTypes->modelKeys())
            ->where('active', 1)
            ->with(['analyte:id,name,code', 'mmethod', 'ltmethod'])
            ->get();

        $stageIds = $elements
            ->pluck('lab_section_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $stageCodes = $stageIds === []
            ? collect()
            : SampleAnalysisStage::query()
                ->whereIn('id', $stageIds)
                ->pluck('code', 'id');

        $testsByTypeId = $elements
            ->groupBy(static fn (AnalysisElements $row): string => (string) $row->analysis_type_id)
            ->map(function ($rows) use ($stageCodes, $analysisTypes): array {
                $analysisType = $analysisTypes->firstWhere('id', (string) $rows->first()?->analysis_type_id);

                return $rows
                    ->filter(static fn (AnalysisElements $row): bool => filled($row->analyte?->name))
                    ->unique(static fn (AnalysisElements $row): string => (string) $row->analyte_id)
                    ->sortBy(static fn (AnalysisElements $row): string => mb_strtolower((string) $row->analyte?->name))
                    ->values()
                    ->map(fn (AnalysisElements $row): array => $this->elementToTestDto(
                        $row,
                        $analysisType,
                        $stageCodes
                    ))
                    ->all();
            });

        $groups = [];
        foreach ($analysisTypes as $analysisType) {
            $groups[] = [
                'analysis_type_id' => (string) $analysisType->id,
                'analysis_type' => (string) $analysisType->name,
                'sample_type' => (string) ($analysisType->sample_type?->name ?? 'Sample type'),
                'tests' => $testsByTypeId->get((string) $analysisType->id, []),
            ];
        }

        return $groups;
    }

    /**
     * @param  \Illuminate\Support\Collection<string, string|null>  $stageCodes
     * @return array{
     *     id: string,
     *     name: string,
     *     analysis_type_id: string,
     *     analysis_type: string,
     *     lab_section_code: string,
     *     reporting_unit: string,
     *     lod: string,
     *     loq: string,
     *     mu: string,
     *     tat: string,
     *     method: string
     * }
     */
    public function elementToTestDto(
        AnalysisElements $element,
        ?AnalysisType $analysisType,
        \Illuminate\Support\Collection $stageCodes,
    ): array {
        $reportDisplay = trim((string) ($element->report_display_name ?? ''));
        if ($reportDisplay === '') {
            $reportDisplay = trim((string) ($element->analyte?->plainReportDisplay() ?? $element->analyte?->code ?? ''));
        }
        if ($reportDisplay === '') {
            $reportDisplay = trim((string) ($element->analyte?->name ?? ''));
        }

        $method = trim((string) ($element->mmethod?->name ?? $element->ltmethod?->name ?? ''));
        $stageId = (string) ($element->lab_section_id ?? '');
        $labSectionCode = $stageId !== '' ? trim((string) ($stageCodes->get($stageId) ?? '')) : '';

        return [
            'id' => (string) $element->id,
            'name' => $reportDisplay !== '' ? $reportDisplay : (string) ($element->analyte?->name ?? $element->id),
            'analysis_type_id' => (string) ($analysisType?->id ?? $element->analysis_type_id ?? ''),
            'analysis_type' => (string) ($analysisType?->name ?? ''),
            'lab_section_code' => $labSectionCode,
            'reporting_unit' => trim((string) ($element->reporting_unit ?? '')),
            'lod' => $this->formatNumeric($element->lod),
            'loq' => $this->formatNumeric($element->hod),
            'mu' => $this->formatNumeric($element->measurement_uncertainty),
            'tat' => trim((string) ($element->reporting_time ?? '')),
            'method' => $method,
        ];
    }

    /**
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<array{id: string, name: string}>
     */
    public function flattenGroups(array $groups): array
    {
        return collect($groups)
            ->flatMap(static fn (array $group): array => $group['tests'] ?? [])
            ->unique('id')
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $selected
     * @param  list<array{tests: list<array{id: string, name: string}>}>  $groups
     * @return list<string>
     */
    public function normalizeSelection(array $selected, array $groups): array
    {
        if ($selected === [] || $groups === []) {
            return [];
        }

        $ids = [];
        $idsByName = [];

        foreach ($groups as $group) {
            foreach ($group['tests'] ?? [] as $test) {
                $id = (string) ($test['id'] ?? '');
                $name = mb_strtolower(trim((string) ($test['name'] ?? '')));
                if ($id === '') {
                    continue;
                }
                $ids[$id] = true;
                if ($name !== '') {
                    $idsByName[$name][] = $id;
                }
            }
        }

        $normalized = [];
        foreach ($selected as $token) {
            $token = trim((string) $token);
            if ($token === '') {
                continue;
            }
            if (isset($ids[$token])) {
                $normalized[] = $token;
                continue;
            }

            foreach ($idsByName[mb_strtolower($token)] ?? [] as $id) {
                $normalized[] = $id;
            }
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Keep analysis-element ids that exist in the catalog when group normalization fails.
     *
     * @param  list<string>  $parameterIds
     * @return list<string>
     */
    public function filterKnownParameterIds(array $parameterIds): array
    {
        $parameterIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $parameterIds
        ), static fn (string $id): bool => $id !== '')));

        if ($parameterIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $parameterIds)
            ->where('active', 1)
            ->pluck('id')
            ->map(static fn ($id): string => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @param  list<string>  $parameterIds
     * @return list<string>
     */
    public function deriveAnalysisTypeIds(array $parameterIds): array
    {
        $parameterIds = array_values(array_unique(array_filter(array_map(
            static fn ($id): string => trim((string) $id),
            $parameterIds
        ), static fn (string $id): bool => $id !== '')));

        if ($parameterIds === []) {
            return [];
        }

        return AnalysisElements::query()
            ->whereIn('id', $parameterIds)
            ->where('active', 1)
            ->pluck('analysis_type_id')
            ->map(static fn ($id): string => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Enrich flat select options for multi-column Select2 dropdowns.
     *
     * @param  list<array{value: string, label: string}>  $options
     * @return list<array{value: string, label: string, meta?: string, meta_method?: string, meta_lab?: string, meta_analysis_type?: string}>
     */
    public function enrichSelectOptions(array $options): array
    {
        $ids = collect($options)
            ->map(static fn (array $option): string => (string) ($option['value'] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return $options;
        }

        $elements = AnalysisElements::query()
            ->with(['analyte', 'mmethod', 'ltmethod', 'analysis_type'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy(static fn ($element): string => (string) $element->id);

        $stageIds = $elements->pluck('lab_section_id')->filter()->unique()->values()->all();
        $stageCodes = $stageIds === []
            ? collect()
            : SampleAnalysisStage::query()->whereIn('id', $stageIds)->pluck('code', 'id');

        return collect($options)->map(function (array $option) use ($elements, $stageCodes): array {
            $id = (string) ($option['value'] ?? '');
            $element = $elements->get($id);
            if ($element === null) {
                return $option;
            }

            $dto = $this->elementToTestDto(
                $element,
                $element->analysis_type,
                $stageCodes
            );

            $option['label'] = $dto['name'];
            $option['meta'] = $dto['analysis_type'] !== '' ? $dto['analysis_type'] : $dto['method'];
            $option['meta_analysis_type'] = $dto['analysis_type'];
            $option['meta_method'] = $dto['method'];
            $option['meta_lab'] = $dto['lab_section_code'];

            return $option;
        })->values()->all();
    }

    private function formatNumeric(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (! is_numeric($value)) {
            return trim((string) $value);
        }

        $float = (float) $value;

        return rtrim(rtrim(number_format($float, 6, '.', ''), '0'), '.');
    }
}
