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
     *   analysis_elements?: Collection<int, AnalysisElements>|null
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
        $analysisElements = $context['analysis_elements'] ?? null;

        if ($analysisElements === null) {
            $query = AnalysisElements::query()
                ->with('analyte')
                ->where('analysis_type_id', $analysisTypeId)
                ->where('active', 1);

            if ($analysisElementIds !== null && $analysisElementIds !== []) {
                $query->whereIn('id', $analysisElementIds);
            }

            $analysisElements = $query->get();
        } elseif ($analysisElementIds !== null && $analysisElementIds !== []) {
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
        $analysisTypeHasNoResultCapture = $analysisType ? (int) ($analysisType->has_no_result ?? 0) : 0;
        $userId = $actingUserId ?? (string) (auth()->id() ?? '')
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

            // Capture Results always prefers the Analysis Element lab section (master data).
            $labSectionId = $this->resolveValidLabSectionId($element->lab_section_id)
                ?? $labSectionIdFromAnalysisType
                ?? $resolvedLabSectionOverride;

            if ($labSectionId === null) {
                throw new \InvalidArgumentException(
                    'Lab section is required to create captured results. Set lab_section_id on analysis element '
                    . (string) ($element->id ?? '')
                    . ' or analysis type '
                    . (string) $analysisTypeId
                    . '.'
                );
            }

            $subcontractedLabByElement = $context['subcontracted_lab_by_element'] ?? [];
            $assignedLabId = isset($subcontractedLabByElement[(string) $element->id])
                ? trim((string) $subcontractedLabByElement[(string) $element->id])
                : '';
            $isSubcontracted = $assignedLabId !== ''
                ? 1
                : $this->resolveSubcontractedFlag($element, $lab, $elementFlagOverrides);

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
                'operator_id' => null,
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
        if ($candidate === null || $candidate === '' || $candidate === '0' || $candidate === 0) {
            return null;
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
}
