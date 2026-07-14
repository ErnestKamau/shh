<?php

namespace App\Services\Sampleworkflow;

use App\CapturedResult;
use App\Result;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;

class ProcessedResultSyncService
{
    /**
     * Copy captured_results into the pre-created results reporting slots for a batch.
     *
     * Unit resolution prefers the per-instance captured reporting_unit_id,
     * falling back to analysis_elements.reporting_unit.
     *
     * @param  array{apply_lod_formatting?: bool}  $options
     */
    public function syncBatch(string $batchId, array $options = []): SampleHeader
    {
        $applyLodFormatting = (bool) ($options['apply_lod_formatting'] ?? false);

        return DB::transaction(function () use ($batchId, $applyLodFormatting) {
            $this->deleteOrphanResults($batchId);

            $capturedRows = CapturedResult::query()
                ->leftJoin('analysis_elements as ae', function ($join) {
                    $join->on('ae.analyte_id', '=', 'captured_results.analyte_id')
                        ->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                })
                ->leftJoin('reporting_units as ru', 'ru.id', '=', 'captured_results.reporting_unit_id')
                ->selectRaw('
                    captured_results.*,
                    ae.decimal_places as ae_decimal_places,
                    ae.significant_figures as ae_significant_figures,
                    ae.reporting_unit as ae_reporting_unit,
                    ae.lod as ae_lod,
                    ae.hod as ae_hod,
                    ae.level as ae_level,
                    ru.name as resolved_reporting_unit_name
                ')
                ->where('captured_results.sample_header_id', $batchId)
                ->whereNotNull('captured_results.result')
                ->orderBy('ae.level')
                ->get();

            foreach ($capturedRows as $captured) {
                $this->syncCapturedRow($captured, $applyLodFormatting);
            }

            $header = SampleHeader::query()->findOrFail($batchId);
            $header->set_date('Processing Date', \Carbon\Carbon::now(), true);

            return $header;
        });
    }

    private function deleteOrphanResults(string $batchId): void
    {
        $results = Result::query()->where('sample_header_id', $batchId)->get();

        foreach ($results as $result) {
            if (! CapturedResult::query()->whereKey($result->captured_result_id)->exists()) {
                $result->delete();
            }
        }
    }

    private function syncCapturedRow(CapturedResult $captured, bool $applyLodFormatting): Result
    {
        $resultValue = $captured->result;
        $type = gettype($resultValue);
        if ($type === 'integer' || $type === 'double') {
            $resultValue = floatval($resultValue);
        }

        $reportingSymbol = (string) ($captured->result_reporting_symbol ?? '');

        if ($applyLodFormatting && is_numeric($resultValue)) {
            $numeric = floatval($resultValue);
            $lod = $captured->ae_lod ?? null;
            $hod = $captured->ae_hod ?? null;
            $significantFigures = $captured->ae_significant_figures ?? null;
            $decimalPlaces = $captured->ae_decimal_places ?? null;

            $reportingSymbol = '';

            if ($lod && $numeric < floatval($lod)) {
                $numeric = $lod;
                $reportingSymbol = '<';
            }

            if ($hod && $numeric > floatval($hod)) {
                $numeric = $hod;
                $reportingSymbol = '>';
            }

            if (intval($significantFigures) > 0) {
                $numeric = sigFig($numeric, intval($significantFigures));
            } elseif (trim((string) $decimalPlaces) !== '') {
                $numeric = round($numeric, intval($decimalPlaces));
            }

            $resultValue = $numeric;
        }

        $unitCode = $captured->resolved_reporting_unit_name
            ?: ($captured->ae_reporting_unit ?: null);

        if (! $unitCode) {
            $unitCode = $captured->effectiveReportingUnitName();
        }

        $attributes = [
            'sample_detail_code' => $captured->sample_detail_code,
            'sample_detail_id' => $captured->sample_detail_id,
            'sample_header_id' => $captured->sample_header_id,
            'analyte_id' => $captured->analyte_id,
            'analyte_code' => $captured->analyte_code,
            'analysis_type_id' => $captured->analysis_type_id,
            'lab_section_id' => $captured->lab_section_id,
            'parameters_order' => $captured->parameters_order ?? 0,
            'remark_is_manual' => $captured->remark_is_manual ?? false,
            'has_no_result_capture' => $captured->has_no_result_capture ?? false,
            'result' => $resultValue,
            'remarks' => $captured->remark,
            'guide' => $captured->main_value,
            'seond_guide' => $captured->secondary_value,
            'reporting_symbol' => $reportingSymbol,
            'unit_code' => $unitCode,
            'analyte_accredited' => $captured->analyte_accredited,
            'analyte_status_contracted' => $captured->analyte_status_contracted,
            'remark_colour' => $captured->remark_colour,
        ];

        return Result::query()->updateOrCreate(
            ['captured_result_id' => $captured->id],
            $attributes
        );
    }
}
