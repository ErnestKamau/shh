<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSequence;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

/**
 * Hard-deletes technical (sandbox) jobs and cascading related lab data.
 */
class TechnicalBatchPurgeService
{
    /**
     * @return array<string, int>
     */
    public function purge(SampleHeader $batch): array
    {
        if (! (bool) ($batch->is_technical ?? false)) {
            throw new InvalidArgumentException('Only technical (sandbox) batches can be hard-purged.');
        }

        $summary = [
            'captured_results' => 0,
            'results' => 0,
            'sample_details' => 0,
            'sample_headers_deleted' => 0,
            'sample_sequences' => 0,
        ];

        return DB::transaction(function () use ($batch, &$summary): array {
            $headerId = (string) $batch->id;
            $batchCode = (string) $batch->batch_code;

            $detailIds = SampleDetails::query()
                ->where('sample_header_id', $headerId)
                ->pluck('id')
                ->map(fn ($id): string => (string) $id)
                ->values();

            $this->purgeBatchTrees(collect([$headerId]), $detailIds, $summary);

            if ($batchCode !== '' && Schema::hasTable('sample_sequences')) {
                $summary['sample_sequences'] = SampleSequence::query()
                    ->where('batch_code', $batchCode)
                    ->delete();
            }

            return $summary;
        });
    }

    /**
     * Soft-delete production batches; hard-purge technical ones.
     *
     * @param  list<string>  $batchCodes
     * @return array{soft_deleted: int, hard_purged: int, skipped: list<string>}
     */
    public function deleteByBatchCodes(array $batchCodes, bool $allowHardPurgeTechnical = true): array
    {
        $softDeleted = 0;
        $hardPurged = 0;
        $skipped = [];

        foreach ($batchCodes as $code) {
            $code = trim((string) $code);
            if ($code === '') {
                continue;
            }

            $batch = SampleHeader::query()->where('batch_code', $code)->first();
            if ($batch === null) {
                $skipped[] = $code;
                continue;
            }

            if ($allowHardPurgeTechnical && (bool) ($batch->is_technical ?? false)) {
                $this->purge($batch);
                $hardPurged++;
                continue;
            }

            if ((bool) ($batch->is_technical ?? false) && ! $allowHardPurgeTechnical) {
                throw new RuntimeException("Technical batch {$code} requires hard purge permission.");
            }

            $batch->isactive = 0;
            $batch->save();
            $softDeleted++;
        }

        return [
            'soft_deleted' => $softDeleted,
            'hard_purged' => $hardPurged,
            'skipped' => $skipped,
        ];
    }

    /**
     * @param  Collection<int, string>  $sampleHeaderIds
     * @param  Collection<int, string>  $sampleDetailIds
     * @param  array<string, int>  $summary
     */
    private function purgeBatchTrees(Collection $sampleHeaderIds, Collection $sampleDetailIds, array &$summary): void
    {
        if ($sampleHeaderIds->isEmpty()) {
            return;
        }

        $headerIdList = $sampleHeaderIds->all();
        $detailIdList = $sampleDetailIds->all();

        if ($detailIdList !== []) {
            $summary['captured_results'] = $this->deleteWhereIn('captured_results', 'sample_detail_id', $detailIdList);
            $summary['results'] = $this->deleteWhereIn('results', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('captured_view', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_analysis_type_relation', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_analysis_dates', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_captured_worksheet_formulas', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('method_sequence_run_samples', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('qc_results', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('sample_progress', 'sample_detail_id', $detailIdList);
            $this->deleteWhereIn('ser_header_worksheet_sample_relations', 'sample_detail_id', $detailIdList);
        }

        $this->deleteWhereIn('captured_results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('captured_view', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('sample_analysis_type_relation', 'batch_id', $headerIdList);
        $this->deleteWhereIn('sample_analysis_dates', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('sample_captured_worksheet_formulas', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('method_sequence_runs', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('qc_results', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('chain_of_custodies', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('batch_attachments', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_ammendments', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_labsection_approval', 'batch_id', $headerIdList);
        $this->deleteWhereIn('batch_notifications', 'batch_id', $headerIdList);
        $this->deleteWhereIn('import_lab_results', 'batch_id', $headerIdList);
        $this->deleteWhereIn('invoice_details', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('report_header_details', 'sample_header_id', $headerIdList);
        $this->deleteWhereIn('supporting_document_instances', 'sample_header_id', $headerIdList);

        if (Schema::hasTable('analysis_acceptance_forms')) {
            AnalysisAcceptanceForm::query()
                ->whereIn('sample_header_id', $headerIdList)
                ->each(function (AnalysisAcceptanceForm $form): void {
                    $form->lines()->delete();
                    $form->delete();
                });
        }

        if ($detailIdList !== []) {
            $summary['sample_details'] = SampleDetails::query()->whereIn('id', $detailIdList)->delete();
        }

        $summary['sample_headers_deleted'] = SampleHeader::query()->whereIn('id', $headerIdList)->delete();
    }

    /**
     * @param  list<string>  $ids
     */
    private function deleteWhereIn(string $table, string $column, array $ids): int
    {
        if ($ids === [] || ! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return (int) DB::table($table)->whereIn($column, $ids)->delete();
    }
}
