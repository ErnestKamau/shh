<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use App\CapturedResult;
use App\Lab;
use App\ReportingUnit;
use App\Result;
use App\SampleAnalysisTypeRelation;
use App\SampleAnalysisStage;
use App\SampleDetails;
use App\SampleHeader;
use App\StandardAnalytes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SampleAnalysisSetupService
{
    /** @var array<string, bool> */
    private array $labSectionValidityCache = [];

    /** @var array<string, bool> */
    private array $methodValidityCache = [];

    /** @var array<string, ReportingUnit|null> */
    private array $reportingUnitCache = [];

    public function syncAnalysisRelations(SampleHeader $header, SampleDetails $detail, array $analysisTypeIds): void
    {
        $analysisTypeIds = array_values(array_filter(array_unique($analysisTypeIds)));
        if ($analysisTypeIds === []) {
            return;
        }

        SampleAnalysisTypeRelation::query()
            ->where('batch_id', $header->id)
            ->where('sample_detail_id', $detail->id)
            ->whereNotIn('analysis_type_id', $analysisTypeIds)
            ->delete();

        $existing = SampleAnalysisTypeRelation::query()
            ->where('batch_id', $header->id)
            ->where('sample_detail_id', $detail->id)
            ->pluck('analysis_type_id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $insert = [];
        foreach ($analysisTypeIds as $analysisTypeId) {
            if (! in_array((string) $analysisTypeId, $existing, true)) {
                $insert[] = [
                    'id' => (string) Str::uuid7(),
                    'analysis_type_id' => $analysisTypeId,
                    'batch_id' => $header->id,
                    'sample_detail_id' => $detail->id,
                ];
            }
        }

        if ($insert !== []) {
            SampleAnalysisTypeRelation::insert($insert);
        }
    }

    /**
     * @param  array{
     *   sample_header?: ?SampleHeader,
     *   sample_detail?: ?SampleDetails,
     *   analysis_type?: ?AnalysisType,
     *   lab?: ?Lab,
     *   reporting_units_by_key?: array<string, ReportingUnit>,
     *   standards_by_key?: array<string, StandardAnalytes>,
     *   analysis_elements?: Collection<int, AnalysisElements>|null,
     *   analyst_ids_by_element?: array<string, array<string, list<string>>>
     * }  $context
     */
    public function createCapturedResultsForAnalysisType(
        string $batchId,
        string $sampleDetailId,
        string $analysisTypeId,
        string $sampleCode,
        ?string $actingUserId = null,
        ?array $analysisElementIds = null,
        ?array $elementFlagOverrides = null,
        ?string $labSectionOverride = null,
        array $context = [],
    ): void {
        // null = legacy "all active elements for the type".
        // [] = explicit empty selection — create nothing (do not expand to all).
        if (is_array($analysisElementIds) && $analysisElementIds === []) {
            return;
        }

        $analysisElements = $context['analysis_elements'] ?? null;

        if ($analysisElements === null) {
            $query = AnalysisElements::query()
                ->with('analyte')
                ->where('analysis_type_id', $analysisTypeId)
                ->where('active', 1);

            if ($analysisElementIds !== null) {
                $query->whereIn('id', $analysisElementIds);
            }

            $analysisElements = $query->get();
        } elseif ($analysisElementIds !== null) {
            $allowed = array_map('strval', $analysisElementIds);
            $analysisElements = $analysisElements
                ->filter(fn (AnalysisElements $element) => in_array((string) $element->id, $allowed, true))
                ->values();
        }

        if ($analysisElements->isEmpty()) {
            Log::warning('No analysis elements found for analysis type', [
                'analysis_type_id' => $analysisTypeId,
                'sample_detail_id' => $sampleDetailId,
            ]);

            return;
        }

        $sampleHeader = $context['sample_header'] ?? SampleHeader::query()->find($batchId);
        $lab = array_key_exists('lab', $context)
            ? $context['lab']
            : $this->resolveLabForHeader($sampleHeader);
        $sampleDetail = $context['sample_detail'] ?? SampleDetails::query()->find($sampleDetailId);
        $analysisType = $context['analysis_type'] ?? AnalysisType::query()->find($analysisTypeId);
        $labSectionIdFromAnalysisType = $this->resolveValidLabSectionId($analysisType?->lab_section_id);
        $resolvedLabSectionOverride = $this->resolveValidLabSectionId($labSectionOverride);
        $labSectionByElement = is_array($context['lab_section_by_element'] ?? null)
            ? $context['lab_section_by_element']
            : [];
        $userIdByLabSection = is_array($context['user_id_by_lab_section'] ?? null)
            ? $context['user_id_by_lab_section']
            : [];
        $analystIdsByElement = is_array($context['analyst_ids_by_element'] ?? null)
            ? $context['analyst_ids_by_element']
            : [];
        $analysisTypeHasNoResultCapture = $analysisType ? (int) ($analysisType->has_no_result ?? 0) : 0;
        $defaultUserId = $actingUserId ?? (string) (auth()->id() ?? '')
            ?: \App\User::query()->where('active', 1)->value('id');

        /** @var array<string, ReportingUnit> $reportingUnitsByKey */
        $reportingUnitsByKey = $context['reporting_units_by_key'] ?? $this->preloadReportingUnits($analysisElements);

        /** @var array<string, StandardAnalytes> $standardsByKey */
        $standardsByKey = $context['standards_by_key'] ?? $this->preloadStandardsForDetail($sampleDetail, $analysisElements);

        foreach ($analysisElements as $element) {
            $analyteCode = $element->relationLoaded('analyte')
                ? ($element->analyte->code ?? 'UNKNOWN')
                : ($element->analyte->code ?? 'UNKNOWN');

            $standardID = null;
            $secondaryStandardID = null;
            $thirdStandardID = null;

            if ($sampleDetail) {
                if ($sampleDetail->main_standard) {
                    $standardID = $standardsByKey[$this->standardKey($sampleDetail->main_standard, $element->analyte_id)] ?? null;
                }
                if ($sampleDetail->secondary_standard) {
                    $secondaryStandardID = $standardsByKey[$this->standardKey($sampleDetail->secondary_standard, $element->analyte_id)] ?? null;
                }
                if ($sampleDetail->third_standard_id) {
                    $thirdStandardID = $standardsByKey[$this->standardKey($sampleDetail->third_standard_id, $element->analyte_id)] ?? null;
                }
            }

            $reportingUnit = $this->resolveReportingUnitFromElement($element, $reportingUnitsByKey);

            $elementId = (string) ($element->id ?? '');
            $acceptanceSectionIds = $this->resolveLabSectionIdsFromElementMap(
                $labSectionByElement[$elementId] ?? null
            );

            $fallbackLabSectionId = $acceptanceSectionIds === []
                ? (
                    $this->resolveValidLabSectionId($element->lab_section_id)
                    ?? $labSectionIdFromAnalysisType
                    ?? $resolvedLabSectionOverride
                    ?? $this->resolveFallbackLabSectionId($analysisTypeId, $analysisType, $lab, $sampleHeader)
                )
                : null;

            $labSectionIdsForElement = $acceptanceSectionIds !== []
                ? $acceptanceSectionIds
                : ($fallbackLabSectionId !== null ? [$fallbackLabSectionId] : []);

            if ($labSectionIdsForElement === []) {
                throw new \InvalidArgumentException(
                    'Lab section is required to create captured results. Set lab_section_id on analysis element '
                    . (string) ($element->id ?? '')
                    . ' or analysis type '
                    . (string) $analysisTypeId
                    . ', or create at least one active lab section.'
                );
            }

            $subcontractedLabByElement = $context['subcontracted_lab_by_element'] ?? [];
            $assignedLabId = isset($subcontractedLabByElement[(string) $element->id])
                ? trim((string) $subcontractedLabByElement[(string) $element->id])
                : '';
            $isSubcontracted = $assignedLabId !== ''
                ? 1
                : $this->resolveSubcontractedFlag($element, $lab, $elementFlagOverrides);

            foreach ($labSectionIdsForElement as $labSectionId) {
                $elementAnalystsBySection = is_array($analystIdsByElement[$elementId] ?? null)
                    ? $analystIdsByElement[$elementId]
                    : [];
                $assignedAnalystIds = array_values(array_unique(array_filter(array_map(
                    'strval',
                    is_array($elementAnalystsBySection[$labSectionId] ?? null)
                        ? $elementAnalystsBySection[$labSectionId]
                        : []
                ))));
                $sectionUserId = (string) ($userIdByLabSection[$labSectionId] ?? '');
                $userId = (string) ($assignedAnalystIds[0] ?? '');
                if ($userId === '') {
                    $userId = ($sectionUserId !== '' && Str::isUuid($sectionUserId))
                        ? $sectionUserId
                        : $defaultUserId;
                }
                $operatorId = trim((string) ($element->operator_id ?? ''));
                if ($operatorId === '') {
                    $operatorId = $userId;
                }

                $capturedResult = new CapturedResult();
                $capturedResult->fill([
                    'sample_detail_code' => $sampleCode,
                    'sample_detail_id' => $sampleDetailId,
                    'sample_header_id' => $batchId,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $analyteCode,
                    'analysis_element_id' => $element->id,
                    'equipment_id' => (empty($element->equipment_id) || $element->equipment_id === '0' || $element->equipment_id === 0) ? null : $element->equipment_id,
                    'result' => null,
                    'user_id' => $userId,
                    'analysis_type_id' => $analysisTypeId,
                    'operator_id' => $operatorId !== '' ? $operatorId : null,
                    'assigned_analyst_ids' => $assignedAnalystIds !== []
                        ? $assignedAnalystIds
                        : ($userId !== '' ? [$userId] : null),
                    'method_id' => $this->resolveValidMethodId($element->method),
                    'reporting_unit_id' => $reportingUnit?->id,
                    'ltm_method_id' => $this->resolveValidMethodId($element->ltm_method_id),
                    'analyte_accredited' => $this->resolveAccreditedFlag($element, $elementFlagOverrides),
                    'analyte_status_contracted' => $isSubcontracted,
                    'subcontracted_lab_id' => $assignedLabId !== '' ? $assignedLabId : null,
                    'lab_section_id' => $labSectionId,
                    'parameters_order' => $element->level ?? 0,
                    'remark_is_manual' => $element->remark_is_manual,
                    'remark' => null,
                    'main_standard_id' => $this->resolveCapturedResultStandardId($sampleDetail, $standardID, 'main_standard'),
                    'secondary_standard_id' => $this->resolveCapturedResultStandardId($sampleDetail, $secondaryStandardID, 'secondary_standard'),
                    'third_standard_id' => $this->resolveCapturedResultStandardId($sampleDetail, $thirdStandardID, 'third_standard_id'),
                    'analysis_type_order' => $element->analysis_type_order ?? 0,
                    'remark_colour' => null,
                    'repeat_captured_id' => null,
                    'has_no_result_capture' => $analysisTypeHasNoResultCapture,
                ]);
                $capturedResult->save();

                $result = new Result();
                $result->fill([
                    'captured_result_id' => $capturedResult->id,
                    'sample_detail_code' => $sampleCode,
                    'sample_detail_id' => $sampleDetailId,
                    'sample_header_id' => $batchId,
                    'analyte_id' => $element->analyte_id,
                    'analyte_code' => $analyteCode,
                    'analysis_type_id' => $analysisTypeId,
                    'unit_code' => $reportingUnit?->name ?? $element->reporting_unit,
                    'reporting_symbol' => $element->reporting_symbol ?? null,
                    'recheck' => 0,
                    'analyte_status_contracted' => $isSubcontracted,
                    'lab_section_id' => $labSectionId,
                    'parameters_order' => $element->level ?? 0,
                    'remark_is_manual' => $element->remark_is_manual,
                    'has_no_result_capture' => $analysisTypeHasNoResultCapture,
                ]);
                $result->save();
            }
        }
    }

    /**
     * Preload analysis elements (with analytes) for many analysis types at once.
     *
     * @param  list<string>  $analysisTypeIds
     * @return Collection<string, Collection<int, AnalysisElements>>
     */
    public function preloadElementsByAnalysisType(array $analysisTypeIds): Collection
    {
        $analysisTypeIds = array_values(array_filter(array_unique(array_map('strval', $analysisTypeIds))));
        if ($analysisTypeIds === []) {
            return collect();
        }

        return AnalysisElements::query()
            ->with('analyte')
            ->whereIn('analysis_type_id', $analysisTypeIds)
            ->where('active', 1)
            ->get()
            ->groupBy(fn (AnalysisElements $element) => (string) $element->analysis_type_id);
    }

    /**
     * @param  Collection<int, AnalysisElements>  $analysisElements
     * @return array<string, ReportingUnit>
     */
    public function preloadReportingUnits(Collection $analysisElements): array
    {
        $keys = $analysisElements
            ->pluck('reporting_unit')
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        if ($keys === []) {
            return [];
        }

        $uuids = [];
        $names = [];
        foreach ($keys as $key) {
            if (Str::isUuid($key)) {
                $uuids[] = $key;
            } else {
                $names[] = $key;
            }
        }

        $units = ReportingUnit::query()
            ->where(function ($query) use ($uuids, $names) {
                if ($uuids !== []) {
                    $query->whereIn('id', $uuids);
                }
                if ($names !== []) {
                    $query->orWhereIn('name', $names);
                }
            })
            ->get();

        $map = [];
        foreach ($units as $unit) {
            $map[(string) $unit->id] = $unit;
            $map[(string) $unit->name] = $unit;
        }

        return $map;
    }

    /**
     * @param  Collection<int, AnalysisElements>  $analysisElements
     * @return array<string, StandardAnalytes>
     */
    public function preloadStandardsForDetail(?SampleDetails $sampleDetail, Collection $analysisElements): array
    {
        if (! $sampleDetail) {
            return [];
        }

        $standardIds = collect([
            $sampleDetail->main_standard,
            $sampleDetail->secondary_standard,
            $sampleDetail->third_standard_id,
        ])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        $analyteIds = $analysisElements
            ->pluck('analyte_id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();

        if ($standardIds === [] || $analyteIds === []) {
            return [];
        }

        $map = [];
        $rows = StandardAnalytes::query()
            ->whereIn('standard_id', $standardIds)
            ->whereIn('analyte_id', $analyteIds)
            ->get();

        foreach ($rows as $row) {
            $map[$this->standardKey($row->standard_id, $row->analyte_id)] = $row;
        }

        return $map;
    }

    private function resolveReportingUnitFromElement(AnalysisElements $element, array $reportingUnitsByKey): ?ReportingUnit
    {
        $reportingUnitValue = $element->reporting_unit;
        if (! $reportingUnitValue) {
            return null;
        }

        $key = (string) $reportingUnitValue;
        if (isset($reportingUnitsByKey[$key])) {
            return $reportingUnitsByKey[$key];
        }

        if (array_key_exists($key, $this->reportingUnitCache)) {
            return $this->reportingUnitCache[$key];
        }

        $unitId = ensureReportingUnitIdFromName($key);
        $unit = $unitId ? ReportingUnit::query()->find($unitId) : null;

        $this->reportingUnitCache[$key] = $unit;
        if ($unit) {
            $this->reportingUnitCache[(string) $unit->id] = $unit;
            $this->reportingUnitCache[(string) $unit->name] = $unit;
        }

        return $unit;
    }

    private function standardKey(mixed $standardId, mixed $analyteId): string
    {
        return (string) $standardId.'|'.(string) $analyteId;
    }

    private function resolveValidLabSectionId(mixed $candidate): ?string
    {
        if (is_array($candidate)) {
            foreach ($candidate as $item) {
                $resolved = $this->resolveValidLabSectionId($item);
                if ($resolved !== null) {
                    return $resolved;
                }
            }

            return null;
        }

        if ($candidate === null || $candidate === '' || $candidate === '0' || $candidate === 0) {
            return null;
        }

        // Multi-section assignments store a list; use the primary section for captured results.
        if (is_array($candidate)) {
            $candidate = $candidate[0] ?? null;
            if ($candidate === null || $candidate === '' || is_array($candidate)) {
                return null;
            }
        }

        $id = (string) $candidate;
        if (! Str::isUuid($id)) {
            return null;
        }

        if (! array_key_exists($id, $this->labSectionValidityCache)) {
            $this->labSectionValidityCache[$id] = SampleAnalysisStage::query()->whereKey($id)->exists();
        }

        return $this->labSectionValidityCache[$id] ? $id : null;
    }

    /**
     * @return list<string>
     */
    private function resolveLabSectionIdsFromElementMap(mixed $candidate): array
    {
        if ($candidate === null || $candidate === '' || $candidate === '0' || $candidate === 0) {
            return [];
        }

        $values = is_array($candidate) ? $candidate : [$candidate];
        $ids = [];
        foreach ($values as $value) {
            $resolved = $this->resolveValidLabSectionId($value);
            if ($resolved !== null) {
                $ids[] = $resolved;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Last-resort lab section when element / analysis type master data is incomplete.
     */
    private function resolveFallbackLabSectionId(
        string $analysisTypeId,
        ?AnalysisType $analysisType,
        ?Lab $lab,
        ?SampleHeader $sampleHeader,
    ): ?string {
        $siblingSectionId = AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', 1)
            ->whereNotNull('lab_section_id')
            ->orderBy('level')
            ->value('lab_section_id');

        $resolved = $this->resolveValidLabSectionId($siblingSectionId);
        if ($resolved !== null) {
            Log::warning('Using sibling analysis-element lab section for captured results', [
                'analysis_type_id' => $analysisTypeId,
                'lab_section_id' => $resolved,
            ]);

            return $resolved;
        }

        if ($sampleHeader !== null) {
            foreach (explode(',', (string) ($sampleHeader->lab_section_ids ?? '')) as $candidate) {
                $resolved = $this->resolveValidLabSectionId(trim($candidate));
                if ($resolved !== null) {
                    Log::warning('Using batch header lab section for captured results', [
                        'analysis_type_id' => $analysisTypeId,
                        'sample_header_id' => $sampleHeader->id,
                        'lab_section_id' => $resolved,
                    ]);

                    return $resolved;
                }
            }
        }

        $labId = trim((string) ($lab?->id ?? $analysisType?->lab_id ?? ''));
        if ($labId === '' && $analysisTypeId !== '') {
            $labId = trim((string) (AnalysisType::query()->whereKey($analysisTypeId)->value('lab_id') ?? ''));
        }
        if ($labId === '') {
            $labId = trim((string) (Lab::defaultLabId() ?? ''));
        }

        if ($labId !== '') {
            $fromLab = SampleAnalysisStage::query()
                ->where('lab_id', $labId)
                ->where(function ($query): void {
                    $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
                })
                ->where('active', true)
                ->orderBy('name')
                ->value('id');

            $resolved = $this->resolveValidLabSectionId($fromLab);
            if ($resolved !== null) {
                Log::warning('Using lab default section for captured results', [
                    'analysis_type_id' => $analysisTypeId,
                    'lab_id' => $labId,
                    'lab_section_id' => $resolved,
                ]);

                return $resolved;
            }
        }

        $anyActive = SampleAnalysisStage::query()
            ->where(function ($query): void {
                $query->where('is_sample_stage', 0)->orWhereNull('is_sample_stage');
            })
            ->where('active', true)
            ->orderBy('name')
            ->value('id');

        $resolved = $this->resolveValidLabSectionId($anyActive);
        if ($resolved !== null) {
            Log::warning('Using first active lab section for captured results', [
                'analysis_type_id' => $analysisTypeId,
                'lab_section_id' => $resolved,
            ]);
        }

        return $resolved;
    }

    /**
     * Avoid FK violations when analysis_elements.method / ltm_method_id
     * still points at a deleted analysis_methods row.
     */
    private function resolveValidMethodId(mixed $candidate): ?string
    {
        if ($candidate === null || $candidate === '' || $candidate === '0' || $candidate === 0) {
            return null;
        }

        $id = (string) $candidate;
        if (! Str::isUuid($id)) {
            return null;
        }

        if (! array_key_exists($id, $this->methodValidityCache)) {
            $this->methodValidityCache[$id] = AnalysisMethod::query()->whereKey($id)->exists();
        }

        return $this->methodValidityCache[$id] ? $id : null;
    }

    private function resolveLabForHeader(?SampleHeader $sampleHeader): ?Lab
    {
        if (! $sampleHeader) {
            return null;
        }

        $labs = $sampleHeader->labs(true);
        if (empty($labs)) {
            return null;
        }

        $labstr = implode(',', $labs);
        $labarr = explode(' - ', $labstr);
        if (count($labarr) >= 2) {
            return Lab::where('code', $labarr[0])->where('name', $labarr[1])->first();
        }

        return null;
    }

    /**
     * captured_results.*_standard_id columns reference standards.id, not standards_analytes.id.
     */
    private function resolveCapturedResultStandardId(
        ?SampleDetails $sampleDetail,
        ?StandardAnalytes $standardAnalyte,
        string $detailAttribute,
    ): ?string {
        if ($standardAnalyte?->standard_id) {
            return (string) $standardAnalyte->standard_id;
        }

        $detailValue = $sampleDetail?->{$detailAttribute};

        return ($detailValue !== null && $detailValue !== '') ? (string) $detailValue : null;
    }

    /**
     * @param  array<string, array{accredited?: bool, subcontracted?: bool}>|null  $elementFlagOverrides
     */
    private function resolveAccreditedFlag(AnalysisElements $element, ?array $elementFlagOverrides): int
    {
        $elementId = (string) $element->id;
        if ($elementFlagOverrides !== null && isset($elementFlagOverrides[$elementId]['accredited'])) {
            return $elementFlagOverrides[$elementId]['accredited'] ? 1 : 0;
        }

        return $element->non_accredited ? 0 : 1;
    }

    /**
     * @param  array<string, array{accredited?: bool, subcontracted?: bool}>|null  $elementFlagOverrides
     */
    private function resolveSubcontractedFlag(AnalysisElements $element, ?Lab $lab, ?array $elementFlagOverrides): int
    {
        $elementId = (string) $element->id;
        if ($elementFlagOverrides !== null && isset($elementFlagOverrides[$elementId]['subcontracted'])) {
            return $elementFlagOverrides[$elementId]['subcontracted'] ? 1 : 0;
        }

        if ((bool) ($element->sub_contracted ?? false)) {
            return 1;
        }

        return (int) ($lab?->is_external ?? 0);
    }

    /**
     * Keep captured/result lab_section_id aligned with analysis element → analysis type master data.
     *
     * Call when sample analysis types change so existing rows do not keep a stale section.
     *
     * @return int Number of captured_results rows updated
     */
    public function syncCapturedResultLabSectionsForSampleDetail(SampleDetails $detail): int
    {
        $detailId = trim((string) ($detail->id ?? ''));
        if ($detailId === '' || ! Str::isUuid($detailId)) {
            return 0;
        }

        $capturedRows = CapturedResult::query()
            ->where('sample_detail_id', $detailId)
            ->get(['id', 'analysis_type_id', 'analysis_element_id', 'analyte_id', 'lab_section_id']);

        if ($capturedRows->isEmpty()) {
            return 0;
        }

        $analysisTypeIds = $capturedRows
            ->pluck('analysis_type_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '' && Str::isUuid($id))
            ->unique()
            ->values()
            ->all();

        $typesById = $analysisTypeIds === []
            ? collect()
            : AnalysisType::query()
                ->whereIn('id', $analysisTypeIds)
                ->get(['id', 'lab_section_id'])
                ->keyBy(fn (AnalysisType $type) => (string) $type->id);

        $elementIds = $capturedRows
            ->pluck('analysis_element_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn (string $id): bool => $id !== '' && Str::isUuid($id))
            ->unique()
            ->values()
            ->all();

        $elementsById = $elementIds === []
            ? collect()
            : AnalysisElements::query()
                ->whereIn('id', $elementIds)
                ->get(['id', 'analysis_type_id', 'analyte_id', 'lab_section_id'])
                ->keyBy(fn (AnalysisElements $element) => (string) $element->id);

        $fallbackElementsByTypeAnalyte = AnalysisElements::query()
            ->whereIn('analysis_type_id', $analysisTypeIds)
            ->where('active', 1)
            ->get(['id', 'analysis_type_id', 'analyte_id', 'lab_section_id'])
            ->groupBy(fn (AnalysisElements $element) => (string) $element->analysis_type_id.'|'.(string) $element->analyte_id);

        $updated = 0;

        foreach ($capturedRows as $captured) {
            $typeId = trim((string) ($captured->analysis_type_id ?? ''));
            $elementId = trim((string) ($captured->analysis_element_id ?? ''));
            $analyteId = trim((string) ($captured->analyte_id ?? ''));

            $element = $elementId !== '' ? $elementsById->get($elementId) : null;
            if ($element === null && $typeId !== '' && $analyteId !== '') {
                $element = $fallbackElementsByTypeAnalyte->get($typeId.'|'.$analyteId)?->first();
            }

            $type = $typeId !== '' ? $typesById->get($typeId) : null;
            $resolvedSectionId = $this->resolveValidLabSectionId($element?->lab_section_id)
                ?? $this->resolveValidLabSectionId($type?->lab_section_id);

            if ($resolvedSectionId === null) {
                continue;
            }

            if ((string) ($captured->lab_section_id ?? '') === $resolvedSectionId) {
                continue;
            }

            CapturedResult::query()
                ->whereKey($captured->id)
                ->update(['lab_section_id' => $resolvedSectionId]);

            Result::query()
                ->where('sample_detail_id', $detailId)
                ->where('analysis_type_id', $typeId !== '' ? $typeId : $captured->analysis_type_id)
                ->when(
                    $analyteId !== '',
                    fn ($query) => $query->where('analyte_id', $analyteId)
                )
                ->update(['lab_section_id' => $resolvedSectionId]);

            $updated++;
        }

        return $updated;
    }

    /**
     * Roll unique analysis-type lab sections onto the batch header (2/polucon style).
     */
    public function syncBatchLabSectionIdsFromAnalysisTypes(SampleHeader $header): ?string
    {
        $analysisTypeIds = SampleAnalysisTypeRelation::query()
            ->where('batch_id', $header->id)
            ->pluck('analysis_type_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($analysisTypeIds === []) {
            $analysisTypeIds = SampleDetails::query()
                ->where('sample_header_id', $header->id)
                ->pluck('analysis_type_id')
                ->flatMap(function ($raw) {
                    return collect(explode(',', (string) $raw))
                        ->map(fn ($id) => trim((string) $id))
                        ->filter();
                })
                ->unique()
                ->values()
                ->all();
        }

        if ($analysisTypeIds === []) {
            return $header->lab_section_ids !== null && $header->lab_section_ids !== ''
                ? (string) $header->lab_section_ids
                : null;
        }

        $sectionIds = AnalysisType::query()
            ->whereIn('id', $analysisTypeIds)
            ->whereNotNull('lab_section_id')
            ->pluck('lab_section_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '' && Str::isUuid($id))
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($sectionIds === []) {
            return $header->lab_section_ids !== null && $header->lab_section_ids !== ''
                ? (string) $header->lab_section_ids
                : null;
        }

        $csv = implode(',', $sectionIds);
        if ((string) $header->lab_section_ids !== $csv) {
            $header->lab_section_ids = $csv;
            $header->save();
        }

        return $csv;
    }

    /**
     * Unique sample_type_ids used by samples on this batch (ordered by name).
     *
     * @return list<string>
     */
    public function sampleTypeIdsFromSamples(SampleHeader $header): array
    {
        $ids = SampleDetails::query()
            ->where('sample_header_id', $header->id)
            ->whereNotNull('sample_type_id')
            ->pluck('sample_type_id')
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '')
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            return [];
        }

        return \App\SampleType::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function sampleTypeLabelsFromSamples(SampleHeader $header): array
    {
        $ids = $this->sampleTypeIdsFromSamples($header);
        if ($ids === []) {
            return [];
        }

        return \App\SampleType::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type) => [
                'id' => (string) $type->id,
                'name' => (string) $type->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Keep sample_headers.sample_type_id as a valid primary among sample rows.
     * Prefer the current primary when it still appears on a sample; otherwise first by name.
     */
    public function syncBatchSampleTypeIdFromSamples(SampleHeader $header): ?string
    {
        $ids = $this->sampleTypeIdsFromSamples($header);
        if ($ids === []) {
            return $header->sample_type_id !== null && $header->sample_type_id !== ''
                ? (string) $header->sample_type_id
                : null;
        }

        $current = trim((string) ($header->sample_type_id ?? ''));
        $primary = in_array($current, $ids, true) ? $current : $ids[0];

        if ((string) ($header->sample_type_id ?? '') !== $primary) {
            $header->sample_type_id = $primary;
            $header->save();
        }

        return $primary;
    }
}
