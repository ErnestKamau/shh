<?php

namespace App\Services\Qc;

use App\CapturedResult;
use App\Models\QcModule\Data\QcResults;
use App\Models\QcModule\QCProcessedResults;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QcBatchCompletionService
{
    public function __construct(
        private readonly QcPassFailEvaluator $passFailEvaluator,
        private readonly QcStatisticsService $statisticsService,
        private readonly QcSchemeResolver $schemeResolver,
    ) {
    }

    /**
     * Rebuild qc_results for a batch, evaluate pass/fail, apply robust stats, mark batch Completed.
     */
    public function completeBatch(string $batchId): SampleHeader
    {
        return DB::transaction(function () use ($batchId) {
            $batch = SampleHeader::query()->with('qctype')->findOrFail($batchId);

            $capturedResults = CapturedResult::query()
                ->with(['sample', 'method', 'sampleHeader'])
                ->where('sample_header_id', $batchId)
                ->get();

            QcResults::query()->where('sample_header_id', $batchId)->delete();

            $systemTolerancePercent = (float) (
                SystemConfiguration::query()
                    ->where('key', 'qc_percentage_config')
                    ->value('value') ?? 0
            );

            $qcResults = [];
            $now = now();

            foreach ($capturedResults as $capturedResult) {
                $analyteProcessed = $this->findOrCreateProcessedGroup($capturedResult, $batch);

                $resolved = $this->schemeResolver->resolve(
                    $capturedResult,
                    $batch->qc_scheme_id ? (string) $batch->qc_scheme_id : null,
                    $batch
                );

                $tolerancePercent = $resolved['repeat_tolerance_percent'] ?? $systemTolerancePercent;

                $status = $this->passFailEvaluator->evaluate(
                    $capturedResult,
                    $tolerancePercent,
                    $resolved['band_standard_id']
                );

                $qcResults[] = [
                    'id' => (string) Str::uuid(),
                    'captured_result_id' => $capturedResult->id,
                    'sample_detail_code' => $capturedResult->sample_detail_code,
                    'sample_detail_id' => $capturedResult->sample_detail_id,
                    'sample_header_id' => $capturedResult->sample_header_id,
                    'analyte_id' => $capturedResult->analyte_id,
                    'analyte_code' => $capturedResult->analyte_code,
                    'result' => $capturedResult->result,
                    'status_code' => $status,
                    'analysis_type_id' => $capturedResult->analysis_type_id,
                    'remarks' => $capturedResult->remark,
                    'analyte_status_contracted' => $capturedResult->analyte_status_contracted,
                    'analyte_accredited' => $capturedResult->analyte_accredited,
                    'qc_scheme_id' => $resolved['primary_scheme_id'] ?? $batch->qc_scheme_id,
                    'qc_type_id' => $batch->qc_type_id,
                    'method_id' => $capturedResult->method_id,
                    'sample_type_id' => $batch->sample_type_id,
                    'repeat_captured_id' => $capturedResult->repeat_captured_id,
                    'previous_result' => $capturedResult->repeatsampleresult,
                    'config_percentage' => $tolerancePercent,
                    'resolved_qc_rules' => $resolved['rules'] !== []
                        ? json_encode($resolved['rules'])
                        : null,
                    'is_qc_processed' => 0,
                    'analyte_processed_id' => $analyteProcessed->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($qcResults !== []) {
                QcResults::query()->insert($qcResults);
            }

            $processedIds = collect($qcResults)
                ->pluck('analyte_processed_id')
                ->unique()
                ->values()
                ->all();

            $this->statisticsService->processAnalyteGroups($processedIds);

            $batch->status = 'Completed';
            $batch->save();

            return $batch->fresh();
        });
    }

    private function findOrCreateProcessedGroup(
        CapturedResult $capturedResult,
        SampleHeader $batch,
    ): QCProcessedResults {
        $existing = QCProcessedResults::query()
            ->where('analyte_id', $capturedResult->analyte_id)
            ->where('analysis_type_id', $capturedResult->analysis_type_id)
            ->where('sample_type_id', $batch->sample_type_id)
            ->where('method_id', $capturedResult->method_id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return QCProcessedResults::query()->create([
            'method_id' => $capturedResult->method_id,
            'analyte_id' => $capturedResult->analyte_id,
            'analysis_type_id' => $capturedResult->analysis_type_id,
            'sample_type_id' => $batch->sample_type_id,
        ]);
    }
}
