<?php

namespace Tests\Feature\Commercial;

use App\DTOs\Commercial\PurchaseOrderAllocationLine;
use App\DTOs\Commercial\PurchaseOrderAllocationResult;
use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\DTOs\Commercial\PurchaseOrderLineBalance;
use App\Enums\Commercial\PurchaseOrderLedgerEntryType;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Events\Commercial\PurchaseOrderLineExhausted;
use App\Events\Commercial\PurchaseOrderLineThresholdReached;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Services\Commercial\PurchaseOrderAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use LogicException;
use Tests\TestCase;

class PurchaseOrderAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseOrderAllocationService $service;

    private string $sampleTypeId;

    private string $packageId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PurchaseOrderAllocationService::class);
        $this->sampleTypeId = (string) Str::uuid();
        $this->packageId = (string) Str::uuid();
    }

    public function test_only_the_remaining_quantity_is_covered_when_more_samples_arrive(): void
    {
        Event::fake([PurchaseOrderLineExhausted::class, PurchaseOrderLineThresholdReached::class]);

        [$po, $line] = $this->purchaseOrderWithLine(100);
        $this->commit($po, 60);

        $result = $this->commit($po, 50);

        $this->assertSame(50, $result->totalRequested());
        $this->assertSame(40, $result->totalCovered());
        $this->assertSame(10, $result->totalUncovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_INSUFFICIENT_BALANCE, $result->lines[0]->uncoveredReason);

        $line->refresh();
        $this->assertSame(100, $line->committed_qty);
        $this->assertSame(0, $line->remaining_qty);
        $this->assertNotNull($line->exhausted_at);
        $this->assertSame(PurchaseOrderStatus::Exhausted, $po->fresh()->status);

        Event::assertDispatchedTimes(PurchaseOrderLineExhausted::class, 1);
    }

    public function test_an_exhausted_po_covers_nothing_more(): void
    {
        [$po] = $this->purchaseOrderWithLine(10);
        $this->commit($po, 10);

        $result = $this->commit($po, 5);

        $this->assertSame(0, $result->totalCovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_INSUFFICIENT_BALANCE, $result->lines[0]->uncoveredReason);
    }

    public function test_a_reservation_is_converted_into_a_commit_at_job_creation(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $enquiryId = (string) Str::uuid();

        $this->service->reserve($po, [$this->demand(30)], $enquiryId);
        $line->refresh();
        $this->assertSame(30, $line->reserved_qty);
        $this->assertSame(70, $line->remaining_qty);

        $result = $this->service->commit($po->fresh(), [$this->demand(30)], (string) Str::uuid(), $enquiryId, now());

        $this->assertSame(30, $result->totalCovered());
        $line->refresh();
        $this->assertSame(0, $line->reserved_qty);
        $this->assertSame(30, $line->committed_qty);
        $this->assertSame(70, $line->remaining_qty);
    }

    public function test_a_fully_reserved_po_can_still_convert_its_reservation(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(20);
        $enquiryId = (string) Str::uuid();

        $this->service->reserve($po, [$this->demand(20)], $enquiryId);
        $this->assertSame(PurchaseOrderStatus::Exhausted, $po->fresh()->status);

        $result = $this->service->commit($po->fresh(), [$this->demand(20)], (string) Str::uuid(), $enquiryId, now());

        $this->assertSame(20, $result->totalCovered());
        $this->assertSame(20, $line->fresh()->committed_qty);
        $this->assertSame(0, $line->fresh()->reserved_qty);
    }

    public function test_reserving_again_replaces_the_previous_reservation(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $enquiryId = (string) Str::uuid();

        $this->service->reserve($po, [$this->demand(30)], $enquiryId);
        $this->service->reserve($po->fresh(), [$this->demand(45)], $enquiryId);

        $line->refresh();
        $this->assertSame(45, $line->reserved_qty);
        $this->assertSame(55, $line->remaining_qty);
    }

    public function test_releasing_a_reservation_returns_the_quantity(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $enquiryId = (string) Str::uuid();

        $this->service->reserve($po, [$this->demand(30)], $enquiryId);
        $this->service->releaseReservation($po->fresh(), $enquiryId, 'Enquiry cancelled');

        $line->refresh();
        $this->assertSame(0, $line->reserved_qty);
        $this->assertSame(100, $line->remaining_qty);
    }

    public function test_committed_quantity_cannot_be_promised_twice(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $this->commit($po, 60);

        $result = $this->service->reserve($po->fresh(), [$this->demand(50)], (string) Str::uuid());

        $this->assertSame(40, $result->totalCovered());
        $this->assertSame(0, $line->fresh()->remaining_qty);
    }

    public function test_marking_units_invoiced_does_not_change_the_remaining_balance(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $sampleHeaderId = (string) Str::uuid();
        $this->commit($po, 40, $sampleHeaderId);

        $this->service->markInvoiced($line->fresh(), 40, (string) Str::uuid(), $sampleHeaderId);

        $line->refresh();
        $this->assertSame(40, $line->committed_qty);
        $this->assertSame(40, $line->invoiced_qty);
        $this->assertSame(60, $line->remaining_qty);
    }

    public function test_invoicing_more_than_committed_is_rejected(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $this->commit($po, 10);

        $this->expectException(PurchaseOrderLedgerException::class);

        $this->service->markInvoiced($line->fresh(), 11, (string) Str::uuid());
    }

    public function test_invoiced_units_must_be_uninvoiced_before_release(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $sampleHeaderId = (string) Str::uuid();
        $this->commit($po, 10, $sampleHeaderId);
        $this->service->markInvoiced($line->fresh(), 10, (string) Str::uuid(), $sampleHeaderId);

        try {
            $this->service->release($line->fresh(), 10, $sampleHeaderId);
            $this->fail('Releasing invoiced units should be rejected.');
        } catch (PurchaseOrderLedgerException) {
            $this->assertSame(10, $line->fresh()->committed_qty);
        }

        $creditNoteId = (string) Str::uuid();
        $this->service->uninvoice($line->fresh(), 10, null, $creditNoteId, 'Credit note');
        $this->service->release($line->fresh(), 10, $sampleHeaderId, $creditNoteId, 'Credit note');

        $line->refresh();
        $this->assertSame(0, $line->committed_qty);
        $this->assertSame(0, $line->invoiced_qty);
        $this->assertSame(100, $line->remaining_qty);
    }

    public function test_releasing_quantity_reactivates_an_exhausted_po(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(10);
        $sampleHeaderId = (string) Str::uuid();
        $this->commit($po, 10, $sampleHeaderId);
        $this->assertSame(PurchaseOrderStatus::Exhausted, $po->fresh()->status);

        $this->service->release($line->fresh(), 4, $sampleHeaderId, null, 'Job cancelled');

        $this->assertSame(PurchaseOrderStatus::Active, $po->fresh()->status);
        $this->assertSame(4, $line->fresh()->remaining_qty);
        $this->assertNull($line->fresh()->exhausted_at);
    }

    public function test_an_expired_po_covers_nothing(): void
    {
        $po = CustomerPurchaseOrder::factory()->expired()->create();
        $this->lineFor($po, 100);

        $result = $this->commit($po, 5);

        $this->assertSame(0, $result->totalCovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_OUTSIDE_VALIDITY, $result->lines[0]->uncoveredReason);
    }

    public function test_samples_received_before_the_po_starts_are_not_covered(): void
    {
        $po = CustomerPurchaseOrder::factory()
            ->validBetween(now()->addWeek()->toDateString(), now()->addYear()->toDateString())
            ->create();
        $this->lineFor($po, 100);

        $result = $this->commit($po, 5);

        $this->assertSame(0, $result->totalCovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_OUTSIDE_VALIDITY, $result->lines[0]->uncoveredReason);
    }

    public function test_the_last_day_of_validity_is_still_covered(): void
    {
        $po = CustomerPurchaseOrder::factory()
            ->validBetween(now()->subMonth()->toDateString(), now()->toDateString())
            ->create();
        $this->lineFor($po, 100);

        $result = $this->service->commit($po, [$this->demand(5)], (string) Str::uuid(), null, now()->endOfDay());

        $this->assertSame(5, $result->totalCovered());
    }

    public function test_a_closed_po_covers_nothing(): void
    {
        $po = CustomerPurchaseOrder::factory()->withStatus(PurchaseOrderStatus::Closed)->create();
        $this->lineFor($po, 100);

        $result = $this->commit($po, 5);

        $this->assertSame(0, $result->totalCovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_PO_NOT_ACTIVE, $result->lines[0]->uncoveredReason);
    }

    public function test_samples_without_a_matching_line_are_not_covered(): void
    {
        [$po] = $this->purchaseOrderWithLine(100);

        $result = $this->service->commit(
            $po,
            [new PurchaseOrderDemandItem('other', (string) Str::uuid(), [$this->packageId], 5)],
            (string) Str::uuid(),
            null,
            now(),
        );

        $this->assertSame(0, $result->totalCovered());
        $this->assertSame(PurchaseOrderAllocationLine::REASON_NO_MATCHING_LINE, $result->lines[0]->uncoveredReason);
    }

    public function test_commit_is_idempotent_for_the_same_job(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $sampleHeaderId = (string) Str::uuid();

        $first = $this->commit($po, 30, $sampleHeaderId);
        $second = $this->commit($po, 30, $sampleHeaderId);

        $this->assertSame(30, $first->totalCovered());
        $this->assertSame(30, $second->totalCovered());
        $this->assertSame(30, $line->fresh()->committed_qty);
        $this->assertSame(1, CustomerPurchaseOrderLedgerEntry::query()
            ->where('sample_header_id', $sampleHeaderId)
            ->where('entry_type', PurchaseOrderLedgerEntryType::Commit->value)
            ->count());
    }

    public function test_a_job_whose_cover_was_fully_released_can_be_committed_again(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $sampleHeaderId = (string) Str::uuid();

        $this->commit($po, 30, $sampleHeaderId);
        $this->service->release($line->fresh(), 30, $sampleHeaderId, null, 'Held as Awaiting PO');

        $again = $this->commit($po, 20, $sampleHeaderId);

        $this->assertSame(20, $again->totalCovered());
        $this->assertSame(20, $line->fresh()->committed_qty);
    }

    public function test_ordered_quantity_cannot_be_reduced_below_what_is_in_use(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $this->commit($po, 80);

        $this->expectException(PurchaseOrderLedgerException::class);

        $this->service->adjustOrderedQuantity($line->fresh(), -30, 'Customer reduced PO');
    }

    public function test_top_up_increases_the_remaining_balance(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $this->commit($po, 100);

        $this->service->adjustOrderedQuantity($line->fresh(), 50, 'Top-up');

        $line->refresh();
        $this->assertSame(150, $line->ordered_qty);
        $this->assertSame(50, $line->remaining_qty);
        $this->assertSame(PurchaseOrderStatus::Active, $po->fresh()->status);
    }

    public function test_cached_balances_always_match_the_ledger(): void
    {
        [$po, $line] = $this->purchaseOrderWithLine(100);
        $enquiryId = (string) Str::uuid();
        $sampleHeaderId = (string) Str::uuid();

        $this->service->reserve($po, [$this->demand(25)], $enquiryId);
        $this->service->commit($po->fresh(), [$this->demand(30)], $sampleHeaderId, $enquiryId, now());
        $this->service->markInvoiced($line->fresh(), 20, (string) Str::uuid(), $sampleHeaderId);
        $this->service->uninvoice($line->fresh(), 5);
        $this->service->release($line->fresh(), 5, $sampleHeaderId);
        $this->service->adjustOrderedQuantity($line->fresh(), 10);
        $this->service->reserve($po->fresh(), [$this->demand(7)], (string) Str::uuid());

        $line->refresh();
        $ledger = $this->service->balanceFromLedger($line);

        $this->assertTrue(PurchaseOrderLineBalance::fromCachedColumns($line)->equals($ledger));
        $this->assertSame($ledger->remaining(), $line->remaining_qty);
        $this->assertSame(110, $ledger->ordered);
        $this->assertSame(7, $ledger->reserved);
        $this->assertSame(25, $ledger->committed);
        $this->assertSame(15, $ledger->invoiced);
        $this->assertSame(78, $ledger->remaining());
    }

    public function test_ledger_entries_cannot_be_updated(): void
    {
        [, $line] = $this->purchaseOrderWithLine(10);
        $entry = CustomerPurchaseOrderLedgerEntry::query()->where('customer_purchase_order_line_id', $line->id)->firstOrFail();

        $this->expectException(LogicException::class);

        $entry->update(['quantity' => 999]);
    }

    public function test_ledger_entries_cannot_be_deleted(): void
    {
        [, $line] = $this->purchaseOrderWithLine(10);
        $entry = CustomerPurchaseOrderLedgerEntry::query()->where('customer_purchase_order_line_id', $line->id)->firstOrFail();

        $this->expectException(LogicException::class);

        $entry->delete();
    }

    public function test_threshold_alert_fires_once_per_crossing(): void
    {
        Event::fake([PurchaseOrderLineThresholdReached::class, PurchaseOrderLineExhausted::class]);

        [$po, $line] = $this->purchaseOrderWithLine(100, notifyAt: 50);

        $crossingJobId = (string) Str::uuid();
        $this->commit($po, 40);
        Event::assertNotDispatched(PurchaseOrderLineThresholdReached::class);

        $this->commit($po, 15, $crossingJobId);
        $this->commit($po, 5);
        Event::assertDispatchedTimes(PurchaseOrderLineThresholdReached::class, 1);
        Event::assertDispatched(PurchaseOrderLineThresholdReached::class, fn (PurchaseOrderLineThresholdReached $event): bool => $event->remainingQty === 45);

        $this->service->release($line->fresh(), 15, $crossingJobId, null, 'Job cancelled');
        $this->assertNull($line->fresh()->threshold_notified_at);

        $this->commit($po, 20);
        Event::assertDispatchedTimes(PurchaseOrderLineThresholdReached::class, 2);
        Event::assertNotDispatched(PurchaseOrderLineExhausted::class);
    }

    public function test_exhausting_a_line_does_not_also_send_a_threshold_alert(): void
    {
        Event::fake([PurchaseOrderLineThresholdReached::class, PurchaseOrderLineExhausted::class]);

        [$po] = $this->purchaseOrderWithLine(100, notifyAt: 50);

        $this->commit($po, 100);

        Event::assertNotDispatched(PurchaseOrderLineThresholdReached::class);
        Event::assertDispatchedTimes(PurchaseOrderLineExhausted::class, 1);
    }

    public function test_adding_a_line_records_an_order_entry(): void
    {
        $po = CustomerPurchaseOrder::factory()->create();

        $line = $this->service->addLine($po, [
            'description' => 'Potable water package',
            'ordered_qty' => 1000,
            'unit_price_gross' => '157.50',
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$this->packageId],
            'is_package' => true,
        ]);

        $this->assertSame(1, $line->line_no);
        $this->assertSame(1000, $line->ordered_qty);
        $this->assertSame(1000, $line->remaining_qty);
        $this->assertSame('157.50', (string) $line->unit_price_gross);
        $this->assertSame(1, $line->ledgerEntries()->where('entry_type', PurchaseOrderLedgerEntryType::Order->value)->count());

        $second = $this->service->addLine($po, ['description' => 'Legionella', 'ordered_qty' => 10]);
        $this->assertSame(2, $second->line_no);
    }

    public function test_a_line_needs_a_positive_ordered_quantity(): void
    {
        $po = CustomerPurchaseOrder::factory()->create();

        $this->expectException(PurchaseOrderLedgerException::class);

        $this->service->addLine($po, ['description' => 'Empty', 'ordered_qty' => 0]);
    }

    /**
     * @return array{0: CustomerPurchaseOrder, 1: CustomerPurchaseOrderLine}
     */
    private function purchaseOrderWithLine(int $orderedQty, ?int $notifyAt = null): array
    {
        $po = CustomerPurchaseOrder::factory()->create();

        return [$po, $this->lineFor($po, $orderedQty, $notifyAt)];
    }

    private function lineFor(CustomerPurchaseOrder $po, int $orderedQty, ?int $notifyAt = null): CustomerPurchaseOrderLine
    {
        return $this->service->addLine($po, [
            'description' => 'Potable water package',
            'ordered_qty' => $orderedQty,
            'unit_price_gross' => 157.50,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$this->packageId],
            'is_package' => true,
            'notify_remaining_qty' => $notifyAt,
        ]);
    }

    private function demand(int $quantity, string $key = 'package'): PurchaseOrderDemandItem
    {
        return new PurchaseOrderDemandItem($key, $this->sampleTypeId, [$this->packageId], $quantity);
    }

    private function commit(CustomerPurchaseOrder $po, int $quantity, ?string $sampleHeaderId = null): PurchaseOrderAllocationResult
    {
        return $this->service->commit(
            $po->fresh(),
            [$this->demand($quantity)],
            $sampleHeaderId ?? (string) Str::uuid(),
            null,
            now(),
        );
    }
}
