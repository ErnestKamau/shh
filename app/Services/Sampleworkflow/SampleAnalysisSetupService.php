<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisElements;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SampleAnalysisSetupService
{
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
            if (!in_array((string) $analysisTypeId, $existing, true)) {
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

    public function createCapturedResultsForAnalysisType(
        string $batchId,
        string $sampleDetailId,
        string $analysisTypeId,
        string $sampleCode,
        ?string $actingUserId = null,
        ?array $analysisElementIds = null,
        ?array $elementFlagOverrides = null,
        ?string $labSectionOverride = null,
    ): void {
        $query = AnalysisElements::query()
            ->where('analysis_type_id', $analysisTypeId)
            ->where('active', 1);

        if ($analysisElementIds !== null && $analysisElementIds !== []) {
            $query->whereIn('id', $analysisElementIds);
        }

        $analysisElements = $query->get();

        if ($analysisElements->isEmpty()) {
            Log::warning('No analysis elements found for analysis type', [
                'analysis_type_id' => $analysisTypeId,
                'sample_detail_id' => $sampleDetailId,
            ]);

            return;
        }

        $sampleHeader = SampleHeader::query()->find($batchId);
        $lab = $this->resolveLabForHeader($sampleHeader);
        $sampleDetail = SampleDetails::query()->find($sampleDetailId);
        $analysisType = AnalysisType::query()->find($analysisTypeId);
        $labSectionIdFromAnalysisType = $this->resolveValidLabSectionId($analysisType?->lab_section_id);
        $resolvedLabSectionOverride = $this->resolveValidLabSectionId($labSectionOverride);
        $analysisTypeHasNoResultCapture = $analysisType ? (int) ($analysisType->has_no_result ?? 0) : 0;
        // user_id is a NOT NULL UUID column; fall back to any active user if unauthenticated.
        $userId = $actingUserId ?? (string) (auth()->id() ?? '')
            ?: \App\User::query()->where('active', 1)->value('id');

        foreach ($analysisElements as $element) {
            $analyteCode = $element->analyte->code ?? 'UNKNOWN';
            $standardID = null;
            $secondaryStandardID = null;
            $thirdStandardID = null;

            if ($sampleDetail) {
                if ($sampleDetail->main_standard) {
                    $standardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->main_standard)->first();
                }
                if ($sampleDetail->secondary_standard) {
                    $secondaryStandardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->secondary_standard)->first();
                }
                if ($sampleDetail->third_standard_id) {
                    $thirdStandardID = StandardAnalytes::where('analyte_id', $element->analyte_id)
                        ->where('standard_id', $sampleDetail->third_standard_id)->first();
                }
            }

            $reportingUnitValue = $element->reporting_unit;
            $reportingUnit = null;
            if ($reportingUnitValue) {
                $isUuid = (bool) preg_match(
                    '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
                    (string) $reportingUnitValue
                );
                $reportingUnit = $isUuid
                    ? ReportingUnit::where('id', $reportingUnitValue)->orWhere('name', $reportingUnitValue)->first()
                    : ReportingUnit::where('name', $reportingUnitValue)->first();
            }

            $labSectionId = $resolvedLabSectionOverride
                ?? $labSectionIdFromAnalysisType
                ?? $this->resolveValidLabSectionId($element->lab_section_id);

            $capturedResult = new CapturedResult();
            $capturedResult->fill([
                'sample_detail_code' => $sampleCode,
                'sample_detail_id' => $sampleDetailId,
                'sample_header_id' => $batchId,
                'analyte_id' => $element->analyte_id,
                'analyte_code' => $analyteCode,
                'equipment_id' => (empty($element->equipment_id) || $element->equipment_id === '0' || $element->equipment_id === 0) ? null : $element->equipment_id,
                'result' => null,
                'user_id' => $userId,
                'analysis_type_id' => $analysisTypeId,
                'operator_id' => null, // captured_results.operator_id is integer; AnalysisElements stores a UUID — incompatible types
                'method_id' => $element->method,
                'reporting_unit_id' => $reportingUnit?->id,
                'ltm_method_id' => $element->ltm_method_id,
                'analyte_accredited' => $this->resolveAccreditedFlag($element, $elementFlagOverrides),
                'analyte_status_contracted' => $this->resolveSubcontractedFlag($element, $lab, $elementFlagOverrides),
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
                'unit_code' => $element->reporting_unit,
                'reporting_symbol' => $element->reporting_symbol ?? null,
                'recheck' => 0,
                'analyte_status_contracted' => $this->resolveSubcontractedFlag($element, $lab, $elementFlagOverrides),
                'lab_section_id' => $labSectionId,
                'parameters_order' => $element->level ?? 0,
                'remark_is_manual' => $element->remark_is_manual,
                'has_no_result_capture' => $analysisTypeHasNoResultCapture,
            ]);
            $result->save();
        }
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

        return SampleAnalysisStage::query()->whereKey($id)->exists() ? $id : null;
    }

    private function resolveLabForHeader(?SampleHeader $sampleHeader): ?Lab
    {
        if (!$sampleHeader) {
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

        return (int) ($lab?->is_external ?? 0);
    }
}
