<?php

namespace App\Services\Sampleworkflow;

use App\ChainOfCustody;
use App\Enums\Commercial\SampleHeaderPoStatus;
use App\SampleDate;
use App\SampleDetails;
use App\SampleHeader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Moves samples without PO cover onto their own "Awaiting PO" job. The new job gets its own
 * job number, but samples keep their codes and the whole group shares the root job's test report.
 */
class SampleHeaderSplitService
{
    public const WORKFLOW_STATUS_AWAITING_PO = 'Awaiting PO';

    /**
     * Tables whose rows follow a sample when it moves to another job.
     *
     * @var array<string, string> table => job foreign key column
     */
    private const SAMPLE_LINKED_TABLES = [
        'captured_results' => 'sample_header_id',
        'sample_analysis_type_relation' => 'batch_id',
        'sample_analysis_dates' => 'sample_header_id',
        'results' => 'sample_header_id',
        'tat_captured' => 'sample_header_id',
        'qc_results' => 'sample_header_id',
        'sample_captured_worksheet_formulas' => 'sample_header_id',
        'method_sequence_run_samples' => 'sample_header_id',
        'grouped_worksheet_results_capture_sample_drafts' => 'sample_header_id',
        'collection_qr_codes' => 'batch_id',
        'test_report_documents' => 'batch_id',
    ];

    /**
     * Columns that describe the source job's lab, billing or reporting progress and must
     * start empty on the held job.
     *
     * @var list<string>
     */
    private const RESET_COLUMNS = [
        'invoice_id',
        'invoice_number',
        'invoice_amount',
        'customer_paid',
        'payment_reference_no',
        'payment_date',
        'payment_done_by',
        'customer_purchase_order_id',
        'verify_user_id',
        'approve_user_id',
        'processing_date',
        'approval_date',
        'email_date',
        'verification_email_date',
        'approval_email_date',
        'in_lab_date',
        'report_verified_date',
        'report_status',
        'batch_report_url',
        'batch_report_online_url',
        'excel_result_url',
        'test_request_report_sequence',
        'po_released_at',
        'po_released_by',
        'po_cancelled_at',
        'po_cancelled_by',
        'po_cancel_reason',
    ];

    public function __construct(
        private readonly JobSampleNumberingService $numberingService,
        private readonly BatchWorkflowStageSyncService $stageSyncService,
        private readonly SampleAnalysisSetupService $analysisSetupService,
    ) {}

    /**
     * Move the given samples off $source onto a new Awaiting PO job in the same report group.
     *
     * @param  list<string>  $sampleDetailIds
     * @param  string|null  $purchaseOrderId  The PO that could not cover the samples, so the job shows under that PO's held jobs.
     */
    public function splitOffAwaitingPo(
        SampleHeader $source,
        array $sampleDetailIds,
        ?string $actingUserId = null,
        ?string $comment = null,
        ?string $purchaseOrderId = null,
    ): SampleHeader {
        $sampleDetailIds = array_values(array_unique(array_filter(array_map('strval', $sampleDetailIds))));
        if ($sampleDetailIds === []) {
            throw new InvalidArgumentException('Select at least one sample to move to the Awaiting PO job.');
        }

        $movableIds = SampleDetails::query()
            ->where('sample_header_id', $source->id)
            ->whereIn('id', $sampleDetailIds)
            ->pluck('id')
            ->map(fn ($id): string => (string) $id)
            ->all();

        if (count($movableIds) !== count($sampleDetailIds)) {
            throw new InvalidArgumentException('Some samples do not belong to job '.$source->batch_code.'.');
        }

        $remaining = SampleDetails::query()->where('sample_header_id', $source->id)->count();
        if ($remaining <= count($movableIds)) {
            throw new InvalidArgumentException('Cannot split every sample off job '.$source->batch_code.'; hold the whole job instead.');
        }

        $actingUserId = $this->resolveActingUserId($source, $actingUserId);

        return DB::transaction(function () use ($source, $movableIds, $actingUserId, $comment, $purchaseOrderId): SampleHeader {
            $held = $this->replicateAsHeld($source, $purchaseOrderId);

            SampleDetails::query()->whereIn('id', $movableIds)->update(['sample_header_id' => $held->id]);
            $this->relinkSampleData($movableIds, (string) $held->id);
            $this->copyTargetDate($source, $held);

            $this->stageSyncService->recordChainOfCustodyTransition(
                $held,
                (string) $held->status,
                is_string($held->sample_tracking_stage) ? $held->sample_tracking_stage : null,
                $comment ?? sprintf('Split from job %s: %d sample(s) awaiting a purchase order.', $source->batch_code, count($movableIds)),
                $actingUserId,
            );

            $this->analysisSetupService->syncBatchLabSectionIdsFromAnalysisTypes($source->fresh() ?? $source);
            $this->analysisSetupService->syncBatchLabSectionIdsFromAnalysisTypes($held->fresh() ?? $held);

            $this->relinkCollectionQrCodesAfterCommit($held);

            return $held;
        });
    }

    /**
     * Mark a job with no PO cover as held without moving samples. The workflow move to
     * Awaiting PO happens when the acceptance is signed.
     *
     * @param  string|null  $purchaseOrderId  The PO that could not cover it, so it shows under that PO's held jobs.
     */
    public function holdWholeJob(SampleHeader $header, ?string $purchaseOrderId = null): void
    {
        $header->forceFill([
            'po_status' => SampleHeaderPoStatus::AwaitingPo->value,
            'po_held_at' => $header->po_held_at ?? now(),
            'customer_purchase_order_id' => $purchaseOrderId,
        ])->save();
    }

    /**
     * The held job inherits the source's workflow status: at creation both wait in Samples Request
     * Review until acceptance is signed; when re-splitting an Awaiting PO job it stays Awaiting PO.
     */
    private function replicateAsHeld(SampleHeader $source, ?string $purchaseOrderId): SampleHeader
    {
        $rootId = filled($source->split_from_sample_header_id)
            ? (string) $source->split_from_sample_header_id
            : (string) $source->id;

        $held = $source->replicate(self::RESET_COLUMNS);
        $held->batch_code = $this->numberingService->generateJobNumber(
            Carbon::now(),
            (bool) ($source->is_technical ?? false),
        );
        $held->split_from_sample_header_id = $rootId;
        $held->customer_purchase_order_id = $purchaseOrderId;
        $held->po_status = SampleHeaderPoStatus::AwaitingPo->value;
        $held->po_held_at = now();
        $held->save();

        return $held;
    }

    /**
     * @param  list<string>  $sampleDetailIds
     */
    private function relinkSampleData(array $sampleDetailIds, string $targetHeaderId): void
    {
        foreach (self::SAMPLE_LINKED_TABLES as $table => $headerColumn) {
            if (! Schema::hasTable($table)
                || ! Schema::hasColumn($table, $headerColumn)
                || ! Schema::hasColumn($table, 'sample_detail_id')) {
                continue;
            }

            DB::table($table)
                ->whereIn('sample_detail_id', $sampleDetailIds)
                ->update([$headerColumn => $targetHeaderId]);
        }
    }

    private function copyTargetDate(SampleHeader $source, SampleHeader $held): void
    {
        $targetDate = SampleDate::query()
            ->where('sample_header_id', $source->id)
            ->where('name', 'Target Date')
            ->first();

        if ($targetDate === null) {
            return;
        }

        $copy = $targetDate->replicate();
        $copy->sample_header_id = $held->id;
        $copy->save();
    }

    private function relinkCollectionQrCodesAfterCommit(SampleHeader $held): void
    {
        DB::afterCommit(static function () use ($held): void {
            try {
                app(CollectionQrCodeService::class)->linkSamplesForBatch($held);
            } catch (\Throwable $e) {
                Log::warning('Collection QR codes could not be linked to the Awaiting PO job.', [
                    'sample_header_id' => (string) $held->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    private function resolveActingUserId(SampleHeader $source, ?string $actingUserId): string
    {
        $candidate = trim((string) ($actingUserId ?: Auth::id() ?: ''));
        if ($candidate !== '' && Str::isUuid($candidate)) {
            return $candidate;
        }

        $previous = trim((string) ChainOfCustody::query()
            ->where('sample_header_id', $source->id)
            ->whereNotNull('moved_in_by')
            ->latest()
            ->value('moved_in_by'));

        if ($previous !== '' && Str::isUuid($previous)) {
            return $previous;
        }

        throw new \RuntimeException('Cannot split job '.$source->batch_code.' without an acting user.');
    }
}
