<?php

namespace App\Services\Commercial;

use App\CapturedResult;
use App\DTOs\Commercial\JobCoverageOutcome;
use App\DTOs\Commercial\PurchaseOrderAllocationResult;
use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\SampleHeaderPoStatus;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\SampleSubmissionRequest;
use App\SampleAnalysisTypeRelation;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use App\Services\Sampleworkflow\SampleHeaderSplitService;
use App\Services\Sampleworkflow\SplitJobReportGroupService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Checks a job's samples against its PO. A sample is covered only when every analysis (or, on
 * per-test lines, every parameter) it needs has PO balance; for customers who need PO cover, uncovered samples are split onto an
 * Awaiting PO job that stays out of the lab until a PO is applied (or it is cancelled).
 */
final class JobPurchaseOrderCoverageService
{
    public const WORKFLOW_STATUS_AWAITING_PO = SampleHeaderSplitService::WORKFLOW_STATUS_AWAITING_PO;

    public const WORKFLOW_STATUS_CANCELLED = 'Cancelled (No PO)';

    public function __construct(
        private readonly PurchaseOrderAllocationService $allocation,
        private readonly EnquiryPurchaseOrderService $enquiryPurchaseOrders,
        private readonly SampleHeaderSplitService $splitter,
        private readonly SplitJobReportGroupService $reportGroups,
        private readonly BatchWorkflowStageSyncService $stageSync,
        private readonly PurchaseOrderDemandBuilder $demandBuilder,
    ) {}

    /**
     * Draw PO cover for a newly created job. Call inside the job-creation transaction, after the
     * samples exist and before the invoice is built. Returns null when PO tracking does not apply.
     */
    public function coverNewJob(SampleHeader $header, ?SampleSubmissionRequest $enquiry, ?string $userId = null): ?JobCoverageOutcome
    {
        if (! EnquiryPurchaseOrderService::enabled() || $enquiry === null) {
            return null;
        }

        $required = $this->enquiryPurchaseOrders->requirement($enquiry) === EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED;
        $po = filled($enquiry->customer_purchase_order_id)
            ? CustomerPurchaseOrder::query()->find((string) $enquiry->customer_purchase_order_id)
            : null;

        [$keysByDetail, $demand] = $this->sampleDemand($header, $po);
        $sampleCount = count($keysByDetail);

        if ($po === null) {
            return $this->finishWithoutCover($header, $required, $sampleCount);
        }

        try {
            return DB::transaction(fn (): JobCoverageOutcome => $this->drawCoverForNewJob(
                $header,
                $enquiry,
                $po,
                $required,
                $keysByDetail,
                $demand,
                $userId,
            ));
        } catch (PurchaseOrderLedgerException $exception) {
            Log::warning('Could not draw PO cover for new job; treating it as uncovered.', [
                'sample_header_id' => (string) $header->id,
                'customer_purchase_order_id' => (string) $po->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->finishWithoutCover($header, $required, $sampleCount);
        }
    }

    /**
     * Cover an Awaiting PO job with an existing PO and release the covered samples to the lab.
     * Samples the PO still cannot cover move to a new Awaiting PO job in the same report group.
     */
    public function applyPurchaseOrder(SampleHeader $held, string $purchaseOrderId, ?string $userId = null): JobCoverageOutcome
    {
        $userId = $this->userId($userId);
        $this->assertHeld($held);

        $po = CustomerPurchaseOrder::query()->find($purchaseOrderId);
        $error = match (true) {
            $po === null => 'Select a purchase order.',
            (string) $po->customer_id !== (string) $held->crm_customer_id => 'The selected PO belongs to another customer.',
            ! $po->acceptsDrawsOn(now()) => 'The selected PO is '.strtolower($po->effectiveStatus()->label()).' and cannot cover new samples.',
            default => null,
        };

        if ($error !== null) {
            throw ValidationException::withMessages(['customer_purchase_order_id' => $error]);
        }

        return DB::transaction(function () use ($held, $po, $userId): JobCoverageOutcome {
            [$keysByDetail, $demand] = $this->sampleDemand($held, $po);

            $result = $this->allocation->commit($po, $demand, (string) $held->id, null, now(), $userId);
            [$coveredIds, $uncoveredIds, $leftover] = $this->assignCover($keysByDetail, $result);

            if ($coveredIds === []) {
                throw ValidationException::withMessages([
                    'customer_purchase_order_id' => sprintf(
                        'PO %s has no balance for the samples on job %s. Top up the PO or add a matching line first.',
                        $po->po_number,
                        $held->batch_code,
                    ),
                ]);
            }

            $this->releaseLeftover($result, $leftover, (string) $held->id, $userId);

            $newHeld = $uncoveredIds !== []
                ? $this->splitter->splitOffAwaitingPo(
                    $held,
                    $uncoveredIds,
                    $userId,
                    sprintf('Split from job %s: PO %s did not cover %d sample(s).', $held->batch_code, $po->po_number, count($uncoveredIds)),
                    (string) $po->id,
                )
                : null;

            $held->forceFill([
                'customer_purchase_order_id' => (string) $po->id,
                'po_status' => SampleHeaderPoStatus::Covered->value,
                'po_released_at' => now(),
                'po_released_by' => $userId,
            ])->save();

            app(AcceptanceFormService::class)->releaseHeldBatchToLab($held->fresh() ?? $held, $userId);

            return new JobCoverageOutcome(
                (string) $held->id,
                SampleHeaderPoStatus::Covered,
                count($coveredIds),
                count($uncoveredIds),
                $newHeld !== null ? (string) $newHeld->id : null,
                (string) $po->id,
                (string) $po->po_number,
            );
        });
    }

    /**
     * Cancel an Awaiting PO job (the customer will not issue a PO). It leaves the report group.
     */
    public function cancelHeldJob(SampleHeader $held, string $reason, ?string $userId = null): void
    {
        $userId = $this->userId($userId);
        $this->assertHeld($held);

        DB::transaction(function () use ($held, $reason, $userId): void {
            $held->forceFill([
                'status' => self::WORKFLOW_STATUS_CANCELLED,
                'sample_tracking_stage' => null,
                'isactive' => 0,
                'po_status' => SampleHeaderPoStatus::Cancelled->value,
                'po_cancelled_at' => now(),
                'po_cancelled_by' => $userId,
                'po_cancel_reason' => trim($reason),
            ])->save();

            $this->stageSync->recordChainOfCustodyTransition(
                $held,
                self::WORKFLOW_STATUS_CANCELLED,
                null,
                'Cancelled — no purchase order: '.trim($reason),
                $userId,
            );
        });
    }

    /**
     * Forecast how many of the held job's samples a PO would release. Writes nothing.
     *
     * @return array{covered: int, held: int}
     */
    public function previewApply(SampleHeader $held, CustomerPurchaseOrder $po): array
    {
        [$keysByDetail, $demand] = $this->sampleDemand($held, $po);
        [$coveredIds, $uncoveredIds] = $this->assignCover($keysByDetail, $this->allocation->preview($po, $demand));

        return ['covered' => count($coveredIds), 'held' => count($uncoveredIds)];
    }

    /**
     * POs (blanket or single) the held job's customer can draw on today.
     *
     * @return Collection<int, CustomerPurchaseOrder>
     */
    public function drawableOrdersFor(SampleHeader $held): Collection
    {
        if (blank($held->crm_customer_id)) {
            return new Collection;
        }

        $today = now()->toDateString();

        return CustomerPurchaseOrder::query()
            ->where('customer_id', (string) $held->crm_customer_id)
            ->whereIn('status', [PurchaseOrderStatus::Active->value, PurchaseOrderStatus::Exhausted->value])
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', $today))
            ->where(fn ($query) => $query->whereNull('valid_to')->orWhereDate('valid_to', '>=', $today))
            ->withSum('lines as remaining_total', 'remaining_qty')
            ->orderBy('po_number')
            ->get();
    }

    /**
     * @return Builder<SampleHeader>
     */
    public function heldJobsQuery(): Builder
    {
        return SampleHeader::query()
            ->where('status', self::WORKFLOW_STATUS_AWAITING_PO)
            ->where('po_status', SampleHeaderPoStatus::AwaitingPo->value)
            ->where('isactive', 1);
    }

    public function isHeld(SampleHeader $header): bool
    {
        return (string) $header->po_status === SampleHeaderPoStatus::AwaitingPo->value;
    }

    /**
     * One-line summary of how a job (and its Awaiting PO parts) came out of the PO check, or null
     * when PO tracking does not apply.
     */
    public function acceptanceSummary(SampleHeader $header): ?string
    {
        if (! EnquiryPurchaseOrderService::enabled() || blank($header->po_status)) {
            return null;
        }

        $members = $this->reportGroups->members($header)->loadCount('samples');

        $hasHeld = $members->contains(fn (SampleHeader $member): bool => $this->isHeld($member));
        if (! $hasHeld && $members->count() === 1 && (string) $header->po_status === SampleHeaderPoStatus::NotRequired->value) {
            return null;
        }

        $purchaseOrders = CustomerPurchaseOrder::query()
            ->whereIn('id', $members->pluck('customer_purchase_order_id')->filter()->unique()->all())
            ->pluck('po_number', 'id');

        return $members
            ->map(function (SampleHeader $member) use ($purchaseOrders): string {
                $count = (int) $member->samples_count;
                $samples = $count === 1 ? '1 sample' : $count.' samples';

                return match ((string) $member->po_status) {
                    SampleHeaderPoStatus::AwaitingPo->value => sprintf('Job %s: Awaiting PO, %s held out of the lab.', $member->batch_code, $samples),
                    SampleHeaderPoStatus::Covered->value => sprintf('Job %s: %s on PO %s.', $member->batch_code, $samples, $purchaseOrders[(string) $member->customer_purchase_order_id] ?? '—'),
                    default => sprintf('Job %s: %s.', $member->batch_code, $samples),
                };
            })
            ->implode(' ');
    }

    /**
     * @param  array<string, list<string>>  $keysByDetail
     * @param  list<PurchaseOrderDemandItem>  $demand
     */
    private function drawCoverForNewJob(
        SampleHeader $header,
        SampleSubmissionRequest $enquiry,
        CustomerPurchaseOrder $po,
        bool $required,
        array $keysByDetail,
        array $demand,
        ?string $userId,
    ): JobCoverageOutcome {
        $receivedAt = filled($header->receipt_date) ? Carbon::parse($header->receipt_date) : now();
        $result = $this->allocation->commit($po, $demand, (string) $header->id, (string) $enquiry->id, $receivedAt, $userId);

        if (! $required) {
            $status = $result->hasUncovered() ? SampleHeaderPoStatus::NotRequired : SampleHeaderPoStatus::Covered;
            $header->forceFill([
                'customer_purchase_order_id' => (string) $po->id,
                'po_status' => $status->value,
            ])->save();

            return new JobCoverageOutcome((string) $header->id, $status, count($keysByDetail), 0, null, (string) $po->id, (string) $po->po_number);
        }

        [$coveredIds, $uncoveredIds, $leftover] = $this->assignCover($keysByDetail, $result);
        $this->releaseLeftover($result, $leftover, (string) $header->id, $userId);

        if ($coveredIds === []) {
            $this->splitter->holdWholeJob($header, (string) $po->id);

            return new JobCoverageOutcome((string) $header->id, SampleHeaderPoStatus::AwaitingPo, 0, count($uncoveredIds), null, (string) $po->id, (string) $po->po_number);
        }

        $held = $uncoveredIds !== []
            ? $this->splitter->splitOffAwaitingPo(
                $header,
                $uncoveredIds,
                $userId,
                sprintf('Split from job %s at creation: PO %s did not cover %d sample(s).', $header->batch_code, $po->po_number, count($uncoveredIds)),
                (string) $po->id,
            )
            : null;

        $header->forceFill([
            'customer_purchase_order_id' => (string) $po->id,
            'po_status' => SampleHeaderPoStatus::Covered->value,
        ])->save();

        return new JobCoverageOutcome(
            (string) $header->id,
            SampleHeaderPoStatus::Covered,
            count($coveredIds),
            count($uncoveredIds),
            $held !== null ? (string) $held->id : null,
            (string) $po->id,
            (string) $po->po_number,
        );
    }

    private function finishWithoutCover(SampleHeader $header, bool $required, int $sampleCount): JobCoverageOutcome
    {
        if ($required && $sampleCount > 0) {
            $this->splitter->holdWholeJob($header);

            return new JobCoverageOutcome((string) $header->id, SampleHeaderPoStatus::AwaitingPo, 0, $sampleCount);
        }

        $header->forceFill(['po_status' => SampleHeaderPoStatus::NotRequired->value])->save();

        return new JobCoverageOutcome((string) $header->id, SampleHeaderPoStatus::NotRequired, $sampleCount, 0);
    }

    /**
     * One demand unit per sample per analysis type, keyed like enquiry demand (sample type | analysis type),
     * split per parameter where the PO prices that parameter per test.
     *
     * @return array{0: array<string, list<string>>, 1: list<PurchaseOrderDemandItem>} sample id => demand keys (in sample code order), demand
     */
    private function sampleDemand(SampleHeader $header, ?CustomerPurchaseOrder $po = null): array
    {
        $details = SampleDetails::query()
            ->where('sample_header_id', $header->id)
            ->orderBy('sample_code')
            ->get(['id', 'sample_code', 'sample_type_id', 'analysis_type_id']);

        $relationTypes = SampleAnalysisTypeRelation::query()
            ->where('batch_id', $header->id)
            ->get(['sample_detail_id', 'analysis_type_id'])
            ->groupBy(fn ($relation): string => (string) $relation->sample_detail_id)
            ->map(fn ($relations): array => $relations->pluck('analysis_type_id')->map(fn ($id): string => trim((string) $id))->filter()->unique()->values()->all());

        $lines = $po?->lines()->get()->all();
        $elementsByDetailAndType = $lines !== null ? $this->requestedElementsBySample($header) : [];

        $keysByDetail = [];
        $totals = [];

        foreach ($details as $detail) {
            $sampleTypeId = $this->idOrNull($detail->sample_type_id ?? $header->sample_type_id);
            $analysisTypeIds = $relationTypes->get((string) $detail->id, []);

            if ($analysisTypeIds === []) {
                $analysisTypeIds = array_values(array_filter(array_map('trim', explode(',', (string) $detail->analysis_type_id))));
            }

            $keys = [];
            foreach ($analysisTypeIds !== [] ? $analysisTypeIds : [null] as $analysisTypeId) {
                $elementIds = $elementsByDetailAndType[(string) $detail->id][(string) $analysisTypeId] ?? [];

                foreach ($this->demandBuilder->unitsFor($sampleTypeId, $analysisTypeId, $elementIds, $lines) as $unit) {
                    $totals[$unit['key']] ??= $unit + ['quantity' => 0];
                    $totals[$unit['key']]['quantity']++;
                    $keys[$unit['key']] = $unit['key'];
                }
            }

            $keysByDetail[(string) $detail->id] = array_values($keys);
        }

        $demand = [];
        foreach ($totals as $key => $total) {
            $demand[] = new PurchaseOrderDemandItem(
                $key,
                $total['sample_type_id'],
                $total['analysis_type_id'] !== null ? [$total['analysis_type_id']] : [],
                $total['quantity'],
                $total['element_ids'],
            );
        }

        return [$keysByDetail, $demand];
    }

    /**
     * Parameters set up on each sample of the job, from its result rows.
     *
     * @return array<string, array<string, list<string>>> sample id => analysis type id => element ids
     */
    private function requestedElementsBySample(SampleHeader $header): array
    {
        $elements = [];

        CapturedResult::query()
            ->where('sample_header_id', $header->id)
            ->whereNotNull('analysis_element_id')
            ->distinct()
            ->get(['sample_detail_id', 'analysis_type_id', 'analysis_element_id'])
            ->each(function (CapturedResult $row) use (&$elements): void {
                $elementId = (string) $row->analysis_element_id;
                $elements[(string) $row->sample_detail_id][(string) $row->analysis_type_id][$elementId] = $elementId;
            });

        return array_map(
            static fn (array $byType): array => array_map(static fn (array $ids): array => array_values($ids), $byType),
            $elements,
        );
    }

    /**
     * Hand covered units to samples in order; a sample is covered only if all its analyses are.
     *
     * @param  array<string, list<string>>  $keysByDetail
     * @return array{0: list<string>, 1: list<string>, 2: array<string, int>} covered sample ids, uncovered sample ids, units left per demand key
     */
    private function assignCover(array $keysByDetail, PurchaseOrderAllocationResult $result): array
    {
        $capacity = [];
        foreach ($result->lines as $line) {
            $capacity[$line->demandKey] = $line->covered;
        }

        $covered = [];
        $uncovered = [];

        foreach ($keysByDetail as $detailId => $keys) {
            $fits = $keys !== [] && array_reduce($keys, fn (bool $carry, string $key): bool => $carry && ($capacity[$key] ?? 0) > 0, true);

            if (! $fits) {
                $uncovered[] = $detailId;

                continue;
            }

            foreach ($keys as $key) {
                $capacity[$key]--;
            }
            $covered[] = $detailId;
        }

        return [$covered, $uncovered, $capacity];
    }

    /**
     * Give back units drawn for analyses whose samples ended up uncovered, so they stay on the PO.
     *
     * @param  array<string, int>  $leftover
     */
    private function releaseLeftover(PurchaseOrderAllocationResult $result, array $leftover, string $sampleHeaderId, ?string $userId): void
    {
        foreach ($leftover as $key => $quantity) {
            $lineId = $result->forDemand($key)?->lineId;
            if ($quantity <= 0 || $lineId === null) {
                continue;
            }

            $line = CustomerPurchaseOrderLine::query()->find($lineId);
            if ($line !== null) {
                $this->allocation->release($line, $quantity, $sampleHeaderId, null, 'Sample held as Awaiting PO: not every analysis was covered', $userId);
            }
        }
    }

    private function assertHeld(SampleHeader $held): void
    {
        if (! $this->isHeld($held) || (string) $held->status !== self::WORKFLOW_STATUS_AWAITING_PO) {
            throw ValidationException::withMessages([
                'job' => sprintf('Job %s is not awaiting a purchase order.', $held->batch_code),
            ]);
        }
    }

    private function idOrNull(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }

    private function userId(?string $userId): ?string
    {
        return $userId ?? (Auth::id() !== null ? (string) Auth::id() : null);
    }
}
