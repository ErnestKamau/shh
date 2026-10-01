<?php

namespace App\Services\Commercial;

use App\DTOs\Commercial\PurchaseOrderAllocationLine;
use App\DTOs\Commercial\PurchaseOrderAllocationResult;
use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\DTOs\Commercial\PurchaseOrderLineBalance;
use App\Enums\Commercial\PurchaseOrderLedgerEntryType as EntryType;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Events\Commercial\PurchaseOrderLineExhausted;
use App\Events\Commercial\PurchaseOrderLineThresholdReached;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The only writer of the customer PO ledger and of the cached balance columns on PO lines.
 *
 * Every write runs in a transaction that locks the PO row first and then the affected
 * lines in id order (fixed lock order, so concurrent jobs cannot deadlock), inserts
 * ledger entries, updates the cached balances, and syncs line / PO status.
 * Threshold and exhausted events are dispatched only after the transaction commits.
 */
final class PurchaseOrderAllocationService
{
    private const TRANSACTION_ATTEMPTS = 3;

    public function __construct(
        private readonly PurchaseOrderLineMatcher $matcher,
    ) {}

    /**
     * Add a line to a PO and record its ordered quantity in the ledger.
     *
     * @param  array{
     *     description: string,
     *     ordered_qty: int,
     *     unit_price_gross?: float|int|string,
     *     sample_type_id?: ?string,
     *     analysis_type_ids?: list<string>,
     *     is_package?: bool,
     *     quotation_detail_id?: ?string,
     *     pricelist_item_id?: ?string,
     *     notify_remaining_qty?: ?int,
     *     line_no?: int
     * }  $attributes
     */
    public function addLine(
        CustomerPurchaseOrder $purchaseOrder,
        array $attributes,
        ?string $userId = null,
        ?string $amendmentId = null,
    ): CustomerPurchaseOrderLine {
        $orderedQty = (int) ($attributes['ordered_qty'] ?? 0);
        if ($orderedQty <= 0) {
            throw new PurchaseOrderLedgerException('A purchase order line needs an ordered quantity greater than zero.');
        }

        return DB::transaction(function () use ($purchaseOrder, $attributes, $orderedQty, $userId, $amendmentId): CustomerPurchaseOrderLine {
            $po = $this->lockPurchaseOrder($purchaseOrder);

            $lineNo = (int) ($attributes['line_no'] ?? 0);
            if ($lineNo <= 0) {
                $lineNo = (int) CustomerPurchaseOrderLine::query()
                    ->where('customer_purchase_order_id', $po->id)
                    ->max('line_no') + 1;
            }

            $line = CustomerPurchaseOrderLine::query()->create([
                'customer_purchase_order_id' => $po->id,
                'line_no' => $lineNo,
                'description' => (string) $attributes['description'],
                'quotation_detail_id' => $attributes['quotation_detail_id'] ?? null,
                'pricelist_item_id' => $attributes['pricelist_item_id'] ?? null,
                'sample_type_id' => $attributes['sample_type_id'] ?? null,
                'analysis_type_ids' => array_values($attributes['analysis_type_ids'] ?? []),
                'is_package' => (bool) ($attributes['is_package'] ?? false),
                'unit_price_gross' => round((float) ($attributes['unit_price_gross'] ?? 0), 2),
                'ordered_qty' => 0,
                'reserved_qty' => 0,
                'committed_qty' => 0,
                'invoiced_qty' => 0,
                'remaining_qty' => 0,
                'notify_remaining_qty' => $attributes['notify_remaining_qty'] ?? null,
            ]);

            $this->applyEntry($po, $line, EntryType::Order, $orderedQty, [
                'amendment_id' => $amendmentId,
            ], null, $userId);

            return $line->fresh() ?? $line;
        }, self::TRANSACTION_ATTEMPTS);
    }

    /**
     * Top up (positive delta) or reduce (negative delta) a line's ordered quantity.
     * A reduction can never take the line below what is already reserved or committed.
     */
    public function adjustOrderedQuantity(
        CustomerPurchaseOrderLine $line,
        int $delta,
        ?string $reason = null,
        ?string $userId = null,
        ?string $amendmentId = null,
    ): CustomerPurchaseOrderLine {
        if ($delta === 0) {
            return $line;
        }

        return $this->onLockedLine($line, function (CustomerPurchaseOrder $po, CustomerPurchaseOrderLine $locked) use ($delta, $reason, $userId, $amendmentId): void {
            $this->applyEntry($po, $locked, EntryType::Adjust, $delta, [
                'amendment_id' => $amendmentId,
            ], $reason, $userId);
        });
    }

    /**
     * Soft-hold quantity for an enquiry (Ready for Reception). Replaces any open
     * reservation the enquiry already holds on this PO, and caps at the remaining balance.
     *
     * @param  iterable<int, PurchaseOrderDemandItem>  $demand
     */
    public function reserve(
        CustomerPurchaseOrder $purchaseOrder,
        iterable $demand,
        string $enquiryId,
        ?CarbonInterface $on = null,
        ?string $userId = null,
    ): PurchaseOrderAllocationResult {
        $demandItems = $this->normaliseDemand($demand);
        $on ??= now();

        return DB::transaction(function () use ($purchaseOrder, $demandItems, $enquiryId, $on, $userId): PurchaseOrderAllocationResult {
            $po = $this->lockPurchaseOrder($purchaseOrder);
            $lines = $this->lockLines($po);

            $this->unreserveOpenReservations($po, $lines, $enquiryId, 'Reservation replaced', $userId);

            $results = $this->drawDemand($po, $lines, $demandItems, $on, function (CustomerPurchaseOrderLine $line, int $qty) use ($po, $enquiryId, $userId): void {
                $this->applyEntry($po, $line, EntryType::Reserve, $qty, [
                    'enquiry_id' => $enquiryId,
                ], null, $userId);
            });

            return new PurchaseOrderAllocationResult((string) $po->id, $results);
        }, self::TRANSACTION_ATTEMPTS);
    }

    /**
     * Read-only forecast of what a reserve / commit would cover right now. Writes nothing and
     * takes no locks. The enquiry's own open reservation counts as available to it.
     *
     * @param  iterable<int, PurchaseOrderDemandItem>  $demand
     */
    public function preview(
        CustomerPurchaseOrder $purchaseOrder,
        iterable $demand,
        ?string $enquiryId = null,
        ?CarbonInterface $on = null,
    ): PurchaseOrderAllocationResult {
        $lines = CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $purchaseOrder->id)
            ->orderBy('id')
            ->get()
            ->keyBy(fn (CustomerPurchaseOrderLine $line): string => (string) $line->id);

        if ($enquiryId !== null && $enquiryId !== '') {
            foreach ($this->openReservationsByLine($purchaseOrder, $enquiryId) as $lineId => $total) {
                $line = $lines->get((string) $lineId);
                if ($line !== null && $total > 0) {
                    $line->remaining_qty = (int) $line->remaining_qty + $total;
                }
            }
        }

        $results = $this->drawDemand(
            $purchaseOrder,
            $lines,
            $this->normaliseDemand($demand),
            $on ?? now(),
            static function (CustomerPurchaseOrderLine $line, int $qty): void {
                $line->remaining_qty = (int) $line->remaining_qty - $qty;
            },
        );

        return new PurchaseOrderAllocationResult((string) $purchaseOrder->id, $results);
    }

    /**
     * Net open reservation the enquiry holds on this PO.
     */
    public function openReservationTotal(CustomerPurchaseOrder $purchaseOrder, string $enquiryId): int
    {
        return array_sum(array_filter($this->openReservationsByLine($purchaseOrder, $enquiryId), static fn (int $total): bool => $total > 0));
    }

    /**
     * Drop every open reservation an enquiry holds on this PO (e.g. enquiry cancelled).
     */
    public function releaseReservation(
        CustomerPurchaseOrder $purchaseOrder,
        string $enquiryId,
        ?string $reason = null,
        ?string $userId = null,
    ): void {
        DB::transaction(function () use ($purchaseOrder, $enquiryId, $reason, $userId): void {
            $po = $this->lockPurchaseOrder($purchaseOrder);
            $lines = $this->lockLines($po);

            $this->unreserveOpenReservations($po, $lines, $enquiryId, $reason ?? 'Reservation released', $userId);
        }, self::TRANSACTION_ATTEMPTS);
    }

    /**
     * Drop every open reservation on the PO, for all enquiries (PO closed or cancelled).
     * Committed quantity is left alone: jobs already received keep their cover.
     *
     * @return int Total quantity unreserved.
     */
    public function releaseAllReservations(
        CustomerPurchaseOrder $purchaseOrder,
        string $reason,
        ?string $userId = null,
    ): int {
        return DB::transaction(function () use ($purchaseOrder, $reason, $userId): int {
            $po = $this->lockPurchaseOrder($purchaseOrder);
            $lines = $this->lockLines($po);

            $enquiryIds = CustomerPurchaseOrderLedgerEntry::query()
                ->where('customer_purchase_order_id', $po->id)
                ->whereNotNull('enquiry_id')
                ->whereIn('entry_type', [EntryType::Reserve->value, EntryType::Unreserve->value])
                ->groupBy('enquiry_id')
                ->havingRaw('SUM(quantity) > 0')
                ->pluck('enquiry_id');

            $before = (int) $lines->sum('reserved_qty');

            foreach ($enquiryIds as $enquiryId) {
                $this->unreserveOpenReservations($po, $lines, (string) $enquiryId, $reason, $userId);
            }

            return $before - (int) $lines->sum('reserved_qty');
        }, self::TRANSACTION_ATTEMPTS);
    }

    /**
     * Binding draw at job creation. Converts the enquiry's open reservation, then covers
     * min(samples received, line remaining) per demand item. Anything not covered is reported
     * back with a reason; nothing is ever drawn beyond the remaining balance.
     *
     * Safe to call again for the same job: if the job already has commit entries on this PO,
     * the existing coverage is returned and nothing new is written.
     *
     * @param  iterable<int, PurchaseOrderDemandItem>  $demand
     */
    public function commit(
        CustomerPurchaseOrder $purchaseOrder,
        iterable $demand,
        string $sampleHeaderId,
        ?string $enquiryId,
        CarbonInterface $receivedAt,
        ?string $userId = null,
    ): PurchaseOrderAllocationResult {
        $demandItems = $this->normaliseDemand($demand);

        return DB::transaction(function () use ($purchaseOrder, $demandItems, $sampleHeaderId, $enquiryId, $receivedAt, $userId): PurchaseOrderAllocationResult {
            $po = $this->lockPurchaseOrder($purchaseOrder);
            $lines = $this->lockLines($po);

            $existing = $this->committedQuantitiesForSampleHeader($po, $sampleHeaderId);
            if ($existing !== null) {
                return $this->rebuildCommitResult($po, $lines, $demandItems, $existing);
            }

            if ($enquiryId !== null && $enquiryId !== '') {
                $this->unreserveOpenReservations($po, $lines, $enquiryId, 'Converted at job creation', $userId);
            }

            $results = $this->drawDemand($po, $lines, $demandItems, $receivedAt, function (CustomerPurchaseOrderLine $line, int $qty) use ($po, $sampleHeaderId, $enquiryId, $userId): void {
                $this->applyEntry($po, $line, EntryType::Commit, $qty, [
                    'enquiry_id' => $enquiryId,
                    'sample_header_id' => $sampleHeaderId,
                ], null, $userId);
            });

            return new PurchaseOrderAllocationResult((string) $po->id, $results);
        }, self::TRANSACTION_ATTEMPTS);
    }

    /**
     * Mark committed units as billed (proforma confirmed). Does not change the remaining balance.
     */
    public function markInvoiced(
        CustomerPurchaseOrderLine $line,
        int $quantity,
        string $invoiceId,
        ?string $sampleHeaderId = null,
        ?string $userId = null,
    ): CustomerPurchaseOrderLine {
        $this->assertPositive($quantity);

        return $this->onLockedLine($line, function (CustomerPurchaseOrder $po, CustomerPurchaseOrderLine $locked) use ($quantity, $invoiceId, $sampleHeaderId, $userId): void {
            $this->applyEntry($po, $locked, EntryType::Invoice, $quantity, [
                'invoice_id' => $invoiceId,
                'sample_header_id' => $sampleHeaderId,
            ], null, $userId);
        });
    }

    /**
     * Reverse a billing mark (invoice cancelled or credited).
     */
    public function uninvoice(
        CustomerPurchaseOrderLine $line,
        int $quantity,
        ?string $invoiceId = null,
        ?string $creditNoteId = null,
        ?string $reason = null,
        ?string $userId = null,
    ): CustomerPurchaseOrderLine {
        $this->assertPositive($quantity);

        return $this->onLockedLine($line, function (CustomerPurchaseOrder $po, CustomerPurchaseOrderLine $locked) use ($quantity, $invoiceId, $creditNoteId, $reason, $userId): void {
            $this->applyEntry($po, $locked, EntryType::Uninvoice, -$quantity, [
                'invoice_id' => $invoiceId,
                'credit_note_id' => $creditNoteId,
            ], $reason, $userId);
        });
    }

    /**
     * Give committed quantity back to the line (job cancelled, credit note for quantity).
     * Billed units must be uninvoiced first, so invoiced never exceeds committed.
     */
    public function release(
        CustomerPurchaseOrderLine $line,
        int $quantity,
        ?string $sampleHeaderId = null,
        ?string $creditNoteId = null,
        ?string $reason = null,
        ?string $userId = null,
    ): CustomerPurchaseOrderLine {
        $this->assertPositive($quantity);

        return $this->onLockedLine($line, function (CustomerPurchaseOrder $po, CustomerPurchaseOrderLine $locked) use ($quantity, $sampleHeaderId, $creditNoteId, $reason, $userId): void {
            $this->applyEntry($po, $locked, EntryType::Release, -$quantity, [
                'sample_header_id' => $sampleHeaderId,
                'credit_note_id' => $creditNoteId,
            ], $reason, $userId);
        });
    }

    /**
     * Balances summed directly from the ledger (source of truth).
     */
    public function balanceFromLedger(CustomerPurchaseOrderLine $line): PurchaseOrderLineBalance
    {
        $sums = CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_line_id', $line->id)
            ->groupBy('entry_type')
            ->selectRaw('entry_type, SUM(quantity) as total')
            ->pluck('total', 'entry_type');

        $sum = static fn (EntryType ...$types): int => array_sum(array_map(
            static fn (EntryType $type): int => (int) ($sums[$type->value] ?? 0),
            $types,
        ));

        return new PurchaseOrderLineBalance(
            $sum(EntryType::Order, EntryType::Adjust),
            $sum(EntryType::Reserve, EntryType::Unreserve),
            $sum(EntryType::Commit, EntryType::Release),
            $sum(EntryType::Invoice, EntryType::Uninvoice),
        );
    }

    /**
     * @param  callable(CustomerPurchaseOrder, CustomerPurchaseOrderLine): void  $callback
     */
    private function onLockedLine(CustomerPurchaseOrderLine $line, callable $callback): CustomerPurchaseOrderLine
    {
        return DB::transaction(function () use ($line, $callback): CustomerPurchaseOrderLine {
            $po = $this->lockPurchaseOrder($line->purchaseOrder()->firstOrFail());

            $locked = CustomerPurchaseOrderLine::query()
                ->whereKey($line->id)
                ->lockForUpdate()
                ->firstOrFail();

            $callback($po, $locked);

            return $locked;
        }, self::TRANSACTION_ATTEMPTS);
    }

    private function lockPurchaseOrder(CustomerPurchaseOrder $purchaseOrder): CustomerPurchaseOrder
    {
        return CustomerPurchaseOrder::query()
            ->whereKey($purchaseOrder->id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * @return Collection<string, CustomerPurchaseOrderLine>
     */
    private function lockLines(CustomerPurchaseOrder $po): Collection
    {
        return CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $po->id)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy(fn (CustomerPurchaseOrderLine $line): string => (string) $line->id);
    }

    /**
     * @param  Collection<string, CustomerPurchaseOrderLine>  $lines
     * @param  list<PurchaseOrderDemandItem>  $demandItems
     * @param  callable(CustomerPurchaseOrderLine, int): void  $draw
     * @return list<PurchaseOrderAllocationLine>
     */
    private function drawDemand(
        CustomerPurchaseOrder $po,
        Collection $lines,
        array $demandItems,
        CarbonInterface $on,
        callable $draw,
    ): array {
        $results = [];

        foreach ($demandItems as $item) {
            if ($item->quantity === 0) {
                $results[] = new PurchaseOrderAllocationLine($item->key, null, 0, 0);

                continue;
            }

            $blockedReason = $this->drawBlockedReason($po, $on);
            if ($blockedReason !== null) {
                $results[] = new PurchaseOrderAllocationLine($item->key, null, $item->quantity, 0, $blockedReason);

                continue;
            }

            $line = $this->matcher->match($lines->values(), $item);
            if ($line === null) {
                $results[] = new PurchaseOrderAllocationLine($item->key, null, $item->quantity, 0, PurchaseOrderAllocationLine::REASON_NO_MATCHING_LINE);

                continue;
            }

            $covered = min($item->quantity, max(0, (int) $line->remaining_qty));
            if ($covered > 0) {
                $draw($line, $covered);
            }

            $results[] = new PurchaseOrderAllocationLine(
                $item->key,
                (string) $line->id,
                $item->quantity,
                $covered,
                $covered < $item->quantity ? PurchaseOrderAllocationLine::REASON_INSUFFICIENT_BALANCE : null,
            );
        }

        return $results;
    }

    private function drawBlockedReason(CustomerPurchaseOrder $po, CarbonInterface $on): ?string
    {
        $status = $po->status ?? PurchaseOrderStatus::Active;

        if (! in_array($status, [PurchaseOrderStatus::Active, PurchaseOrderStatus::Exhausted], true)) {
            return PurchaseOrderAllocationLine::REASON_PO_NOT_ACTIVE;
        }

        if (! $po->isWithinValidity($on)) {
            return PurchaseOrderAllocationLine::REASON_OUTSIDE_VALIDITY;
        }

        return null;
    }

    /**
     * @param  Collection<string, CustomerPurchaseOrderLine>  $lines
     */
    private function unreserveOpenReservations(
        CustomerPurchaseOrder $po,
        Collection $lines,
        string $enquiryId,
        string $reason,
        ?string $userId,
    ): void {
        foreach ($this->openReservationsByLine($po, $enquiryId) as $lineId => $total) {
            $line = $lines->get((string) $lineId);

            if ($total <= 0 || $line === null) {
                continue;
            }

            $this->applyEntry($po, $line, EntryType::Unreserve, -$total, [
                'enquiry_id' => $enquiryId,
            ], $reason, $userId);
        }
    }

    /**
     * @return array<string, int> line id => net reserved quantity for the enquiry
     */
    private function openReservationsByLine(CustomerPurchaseOrder $po, string $enquiryId): array
    {
        return CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('enquiry_id', $enquiryId)
            ->whereIn('entry_type', [EntryType::Reserve->value, EntryType::Unreserve->value])
            ->groupBy('customer_purchase_order_line_id')
            ->selectRaw('customer_purchase_order_line_id, SUM(quantity) as total')
            ->pluck('total', 'customer_purchase_order_line_id')
            ->map(static fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * Net committed quantity per line for a job, or null when the job holds no committed cover on this PO
     * (never committed, or everything committed was released again, e.g. the job was held as Awaiting PO).
     *
     * @return array<string, int>|null
     */
    private function committedQuantitiesForSampleHeader(CustomerPurchaseOrder $po, string $sampleHeaderId): ?array
    {
        $rows = CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('sample_header_id', $sampleHeaderId)
            ->whereIn('entry_type', [EntryType::Commit->value, EntryType::Release->value])
            ->groupBy('customer_purchase_order_line_id')
            ->selectRaw('customer_purchase_order_line_id, SUM(quantity) as total, SUM(CASE WHEN entry_type = ? THEN 1 ELSE 0 END) as commit_count', [EntryType::Commit->value])
            ->get();

        if ((int) $rows->sum('commit_count') === 0) {
            return null;
        }

        $committed = $rows
            ->mapWithKeys(fn ($row): array => [(string) $row->customer_purchase_order_line_id => max(0, (int) $row->total)])
            ->all();

        return array_sum($committed) > 0 ? $committed : null;
    }

    /**
     * @param  Collection<string, CustomerPurchaseOrderLine>  $lines
     * @param  list<PurchaseOrderDemandItem>  $demandItems
     * @param  array<string, int>  $committedByLine
     */
    private function rebuildCommitResult(
        CustomerPurchaseOrder $po,
        Collection $lines,
        array $demandItems,
        array $committedByLine,
    ): PurchaseOrderAllocationResult {
        $results = [];

        foreach ($demandItems as $item) {
            $line = $this->matcher->match($lines->values(), $item);
            if ($line === null) {
                $results[] = new PurchaseOrderAllocationLine($item->key, null, $item->quantity, 0, PurchaseOrderAllocationLine::REASON_NO_MATCHING_LINE);

                continue;
            }

            $lineId = (string) $line->id;
            $covered = min($item->quantity, $committedByLine[$lineId] ?? 0);
            $committedByLine[$lineId] = ($committedByLine[$lineId] ?? 0) - $covered;

            $results[] = new PurchaseOrderAllocationLine(
                $item->key,
                $lineId,
                $item->quantity,
                $covered,
                $covered < $item->quantity ? PurchaseOrderAllocationLine::REASON_INSUFFICIENT_BALANCE : null,
            );
        }

        return new PurchaseOrderAllocationResult((string) $po->id, $results);
    }

    /**
     * Insert one ledger entry and move the matching cached balance, enforcing every invariant
     * before anything is written. Must be called with the PO and line rows locked.
     *
     * @param  array{enquiry_id?: ?string, sample_header_id?: ?string, invoice_id?: ?string, credit_note_id?: ?string, amendment_id?: ?string}  $references
     */
    private function applyEntry(
        CustomerPurchaseOrder $po,
        CustomerPurchaseOrderLine $line,
        EntryType $type,
        int $quantity,
        array $references,
        ?string $reason,
        ?string $userId,
    ): void {
        $this->assertSign($type, $quantity);

        $before = PurchaseOrderLineBalance::fromCachedColumns($line);
        $after = new PurchaseOrderLineBalance(
            $before->ordered + ($type->bucket() === 'ordered' ? $quantity : 0),
            $before->reserved + ($type->bucket() === 'reserved' ? $quantity : 0),
            $before->committed + ($type->bucket() === 'committed' ? $quantity : 0),
            $before->invoiced + ($type->bucket() === 'invoiced' ? $quantity : 0),
        );

        $this->assertInvariants($after, $type);

        CustomerPurchaseOrderLedgerEntry::query()->create([
            'customer_purchase_order_id' => $po->id,
            'customer_purchase_order_line_id' => $line->id,
            'entry_type' => $type,
            'quantity' => $quantity,
            'enquiry_id' => $references['enquiry_id'] ?? null,
            'sample_header_id' => $references['sample_header_id'] ?? null,
            'invoice_id' => $references['invoice_id'] ?? null,
            'credit_note_id' => $references['credit_note_id'] ?? null,
            'amendment_id' => $references['amendment_id'] ?? null,
            'reason' => $reason,
            'created_by' => $userId ?? (Auth::id() !== null ? (string) Auth::id() : null),
        ]);

        $line->fill($after->toColumns());
        $this->syncLineAlerts($line, $before->remaining(), $after->remaining());
        $line->save();

        $this->syncPurchaseOrderStatus($po);
    }

    private function syncLineAlerts(CustomerPurchaseOrderLine $line, int $remainingBefore, int $remainingAfter): void
    {
        $threshold = $line->notify_remaining_qty;

        if ($remainingAfter <= 0) {
            if ($line->exhausted_at === null) {
                $line->exhausted_at = now();
                event(new PurchaseOrderLineExhausted($line));
            }

            if ($threshold !== null && $line->threshold_notified_at === null) {
                $line->threshold_notified_at = now();
            }

            return;
        }

        $line->exhausted_at = null;

        if ($threshold === null) {
            return;
        }

        if ($remainingAfter > $threshold) {
            $line->threshold_notified_at = null;

            return;
        }

        if ($line->threshold_notified_at === null && $remainingAfter < $remainingBefore) {
            $line->threshold_notified_at = now();
            event(new PurchaseOrderLineThresholdReached($line, $remainingAfter));
        }
    }

    /**
     * Active ⇄ Exhausted follows the lines' remaining balances. Expired / closed / cancelled are left alone.
     */
    private function syncPurchaseOrderStatus(CustomerPurchaseOrder $po): void
    {
        $status = $po->status ?? PurchaseOrderStatus::Active;
        if (! in_array($status, [PurchaseOrderStatus::Active, PurchaseOrderStatus::Exhausted], true)) {
            return;
        }

        $hasRemaining = CustomerPurchaseOrderLine::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('remaining_qty', '>', 0)
            ->exists();

        $target = $hasRemaining ? PurchaseOrderStatus::Active : PurchaseOrderStatus::Exhausted;

        if ($status !== $target) {
            $po->status = $target;
            $po->save();
        }
    }

    private function assertInvariants(PurchaseOrderLineBalance $after, EntryType $type): void
    {
        if ($after->ordered < 0 || $after->reserved < 0 || $after->committed < 0 || $after->invoiced < 0) {
            throw new PurchaseOrderLedgerException("A {$type->value} entry would make a purchase order balance negative.");
        }

        if ($after->remaining() < 0) {
            throw new PurchaseOrderLedgerException("A {$type->value} entry would draw beyond the purchase order line's remaining quantity.");
        }

        if ($after->invoiced > $after->committed) {
            throw new PurchaseOrderLedgerException("A {$type->value} entry would leave more units invoiced than committed. Uninvoice them first.");
        }
    }

    private function assertSign(EntryType $type, int $quantity): void
    {
        $valid = match ($type) {
            EntryType::Order, EntryType::Reserve, EntryType::Commit, EntryType::Invoice => $quantity > 0,
            EntryType::Unreserve, EntryType::Release, EntryType::Uninvoice => $quantity < 0,
            EntryType::Adjust => $quantity !== 0,
        };

        if (! $valid) {
            throw new PurchaseOrderLedgerException("Invalid quantity {$quantity} for a {$type->value} ledger entry.");
        }
    }

    private function assertPositive(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new PurchaseOrderLedgerException('Quantity must be greater than zero.');
        }
    }

    /**
     * @param  iterable<int, PurchaseOrderDemandItem>  $demand
     * @return list<PurchaseOrderDemandItem>
     */
    private function normaliseDemand(iterable $demand): array
    {
        $items = [];
        foreach ($demand as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
