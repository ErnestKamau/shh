<?php

namespace Tests\Feature\Commercial;

use App\DTOs\Commercial\PurchaseOrderDemandItem;
use App\Enums\Commercial\PurchaseOrderAmendmentType;
use App\Enums\Commercial\PurchaseOrderInvoicingMode;
use App\Enums\Commercial\PurchaseOrderLedgerEntryType;
use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Events\Commercial\PurchaseOrderLineExhausted;
use App\Events\Commercial\PurchaseOrderLineThresholdReached;
use App\Exceptions\Commercial\PurchaseOrderLedgerException;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderAmendment;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\CRM\CRMCustomer;
use App\QuotationHeader;
use App\Services\Commercial\CustomerPurchaseOrderRegistryService;
use App\Services\Commercial\PurchaseOrderAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerPurchaseOrderRegistryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CustomerPurchaseOrderRegistryService $registry;

    private PurchaseOrderAllocationService $allocation;

    private string $sampleTypeId;

    private string $packageId;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PurchaseOrderLineExhausted::class, PurchaseOrderLineThresholdReached::class]);
        Storage::fake('public');

        $this->registry = app(CustomerPurchaseOrderRegistryService::class);
        $this->allocation = app(PurchaseOrderAllocationService::class);
        $this->sampleTypeId = (string) Str::uuid();
        $this->packageId = (string) Str::uuid();
    }

    public function test_a_blanket_po_is_created_with_its_lines_and_opening_ledger_entries(): void
    {
        $customer = $this->customer();
        $file = UploadedFile::fake()->create('po-2026-114.pdf', 120, 'application/pdf');

        $po = $this->registry->createBlanket(
            $this->attributes($customer, ['po_number' => '  PO-2026-114 ', 'notes' => 'Annual water testing']),
            [
                $this->lineInput(1000, notifyAt: 100),
                $this->lineInput(50, description: 'Legionella'),
            ],
            $file,
        );

        $this->assertSame('PO-2026-114', $po->po_number);
        $this->assertSame(PurchaseOrderType::Blanket, $po->po_type);
        $this->assertSame(PurchaseOrderStatus::Active, $po->status);
        $this->assertSame((string) $customer->id, (string) $po->customer_id);
        $this->assertSame('Annual water testing', $po->notes);
        $this->assertSame('po-2026-114.pdf', $po->file_name);
        Storage::disk('public')->assertExists($po->file_path);

        $lines = $po->lines->sortBy('line_no')->values();
        $this->assertCount(2, $lines);
        $this->assertSame([1, 2], $lines->pluck('line_no')->all());
        $this->assertSame(1000, $lines[0]->remaining_qty);
        $this->assertSame(100, $lines[0]->notify_remaining_qty);
        $this->assertSame('157.50', (string) $lines[0]->unit_price_gross);
        $this->assertSame([$this->packageId], $lines[0]->analysis_type_ids);

        $this->assertSame(2, CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('entry_type', PurchaseOrderLedgerEntryType::Order->value)
            ->count());
    }

    public function test_a_periodic_po_keeps_its_period_and_a_per_job_po_drops_it(): void
    {
        $customer = $this->customer();

        $periodic = $this->registry->createBlanket(
            $this->attributes($customer, ['invoicing_mode' => 'periodic', 'invoicing_period' => 'monthly']),
            [$this->lineInput(10)],
        );
        $perJob = $this->registry->createBlanket(
            $this->attributes($customer, ['invoicing_mode' => 'per_job', 'invoicing_period' => 'monthly']),
            [$this->lineInput(10)],
        );

        $this->assertSame(PurchaseOrderInvoicingMode::Periodic, $periodic->invoicing_mode);
        $this->assertSame('monthly', $periodic->invoicing_period);
        $this->assertSame(PurchaseOrderInvoicingMode::PerJob, $perJob->invoicing_mode);
        $this->assertNull($perJob->invoicing_period);
    }

    public function test_a_blanket_po_needs_at_least_one_line(): void
    {
        $this->assertValidationError('lines', fn () => $this->registry->createBlanket($this->attributes($this->customer()), []));
    }

    public function test_a_duplicate_blanket_po_number_for_the_same_customer_is_rejected_case_insensitively(): void
    {
        $customer = $this->customer();
        $this->registry->createBlanket($this->attributes($customer, ['po_number' => 'PO-77']), [$this->lineInput(10)]);

        $this->assertValidationError('po_number', fn () => $this->registry->createBlanket(
            $this->attributes($customer, ['po_number' => 'po-77']),
            [$this->lineInput(10)],
        ));
    }

    public function test_the_same_po_number_is_allowed_for_another_customer_or_after_cancellation(): void
    {
        $customer = $this->customer();
        $first = $this->registry->createBlanket($this->attributes($customer, ['po_number' => 'PO-77']), [$this->lineInput(10)]);

        $otherCustomerPo = $this->registry->createBlanket($this->attributes($this->customer(), ['po_number' => 'PO-77']), [$this->lineInput(10)]);
        $this->assertSame('PO-77', $otherCustomerPo->po_number);

        $this->registry->cancel($first, 'Captured against the wrong contract');
        $replacement = $this->registry->createBlanket($this->attributes($customer, ['po_number' => 'PO-77']), [$this->lineInput(10)]);

        $this->assertNotSame((string) $first->id, (string) $replacement->id);
    }

    public function test_an_alert_level_must_be_below_the_ordered_quantity(): void
    {
        $this->assertValidationError('lines.1.notify_remaining_qty', fn () => $this->registry->createBlanket(
            $this->attributes($this->customer()),
            [$this->lineInput(100, notifyAt: 10), $this->lineInput(20, notifyAt: 20)],
        ));

        $this->assertSame(0, CustomerPurchaseOrder::query()->count());
    }

    public function test_the_source_quotation_must_belong_to_the_customer(): void
    {
        $quotation = $this->quotationFor($this->customer());

        $this->assertValidationError('quotation_header_id', fn () => $this->registry->createBlanket(
            $this->attributes($this->customer(), ['quotation_header_id' => (string) $quotation->id]),
            [$this->lineInput(10)],
        ));
    }

    public function test_the_po_is_linked_to_its_own_customers_quotation(): void
    {
        $customer = $this->customer();
        $quotation = $this->quotationFor($customer);

        $po = $this->registry->createBlanket(
            $this->attributes($customer, ['quotation_header_id' => (string) $quotation->id]),
            [$this->lineInput(10)],
        );

        $this->assertSame((string) $quotation->id, (string) $po->quotation_header_id);
    }

    public function test_the_uploaded_file_is_removed_when_creation_fails(): void
    {
        $file = UploadedFile::fake()->create('po.pdf', 50, 'application/pdf');

        try {
            $this->registry->createBlanket($this->attributes($this->customer()), [$this->lineInput(0)], $file);
            $this->fail('A zero-quantity line should not be accepted.');
        } catch (PurchaseOrderLedgerException) {
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame(0, CustomerPurchaseOrder::query()->count());
    }

    public function test_a_top_up_is_audited_and_its_ledger_entry_points_at_the_amendment(): void
    {
        [$po, $line] = $this->blanketWithLine(100);

        $amendment = $this->registry->adjustLineQuantity($line, 50, 'Customer extended the contract');

        $this->assertSame(PurchaseOrderAmendmentType::TopUp, $amendment->amendment_type);
        $this->assertSame((string) $line->id, (string) $amendment->customer_purchase_order_line_id);
        $this->assertSame('Customer extended the contract', $amendment->reason);
        $this->assertSame(['from' => 100, 'to' => 150], $amendment->changes['ordered_qty']);
        $this->assertSame(50, $amendment->changes['delta']);

        $line->refresh();
        $this->assertSame(150, $line->ordered_qty);
        $this->assertSame(150, $line->remaining_qty);

        $entry = CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_line_id', $line->id)
            ->where('entry_type', PurchaseOrderLedgerEntryType::Adjust->value)
            ->sole();
        $this->assertSame(50, (int) $entry->quantity);
        $this->assertSame((string) $amendment->id, (string) $entry->amendment_id);
    }

    public function test_a_reduction_is_audited(): void
    {
        [, $line] = $this->blanketWithLine(100);

        $amendment = $this->registry->adjustLineQuantity($line, -30, 'Scope reduced');

        $this->assertSame(PurchaseOrderAmendmentType::Reduce, $amendment->amendment_type);
        $this->assertSame(['from' => 100, 'to' => 70], $amendment->changes['ordered_qty']);
        $this->assertSame(70, $line->fresh()->remaining_qty);
    }

    public function test_a_reduction_below_the_quantity_in_use_is_rejected_without_an_amendment(): void
    {
        [$po, $line] = $this->blanketWithLine(100);
        $this->allocation->reserve($po, [$this->demand(40)], (string) Str::uuid());
        $this->commit($po, 30);

        $this->assertValidationError('quantity', fn () => $this->registry->adjustLineQuantity($line->fresh(), -40, 'Too much'));

        $this->assertSame(0, CustomerPurchaseOrderAmendment::query()->where('customer_purchase_order_id', $po->id)->count());
        $this->assertSame(100, $line->fresh()->ordered_qty);
    }

    public function test_a_zero_adjustment_is_rejected(): void
    {
        [, $line] = $this->blanketWithLine(100);

        $this->assertValidationError('quantity', fn () => $this->registry->adjustLineQuantity($line, 0, 'Nothing'));
    }

    public function test_adding_a_line_is_audited(): void
    {
        [$po] = $this->blanketWithLine(100);

        $line = $this->registry->addLine($po, $this->lineInput(25, description: 'Microbiology'), 'New scope agreed');

        $this->assertSame(2, $line->line_no);
        $this->assertSame(25, $line->remaining_qty);

        $amendment = CustomerPurchaseOrderAmendment::query()
            ->where('customer_purchase_order_id', $po->id)
            ->where('amendment_type', PurchaseOrderAmendmentType::AddLine->value)
            ->sole();
        $this->assertSame((string) $line->id, (string) $amendment->customer_purchase_order_line_id);
        $this->assertSame('Microbiology', $amendment->changes['description']);
        $this->assertSame(25, $amendment->changes['ordered_qty']);

        $this->assertSame((string) $amendment->id, (string) CustomerPurchaseOrderLedgerEntry::query()
            ->where('customer_purchase_order_line_id', $line->id)
            ->where('entry_type', PurchaseOrderLedgerEntryType::Order->value)
            ->value('amendment_id'));
    }

    public function test_extending_an_expired_po_reactivates_it_and_rearms_the_expiry_notice(): void
    {
        $po = CustomerPurchaseOrder::factory()->blanket()->expired()->withStatus(PurchaseOrderStatus::Expired)->create([
            'customer_id' => $this->customer()->id,
            'expiry_notified_at' => now()->subMonth(),
        ]);
        $this->allocation->addLine($po, $this->lineAttributes(10));
        $newEnd = now()->addMonths(6)->toDateString();

        $updated = $this->registry->changeValidity($po, null, $newEnd, 'Contract renewed');

        $this->assertSame(PurchaseOrderStatus::Active, $updated->status);
        $this->assertSame($newEnd, $updated->valid_to->toDateString());
        $this->assertNull($updated->expiry_notified_at);

        $amendment = CustomerPurchaseOrderAmendment::query()->where('customer_purchase_order_id', $po->id)->sole();
        $this->assertSame(PurchaseOrderAmendmentType::ExtendValidity, $amendment->amendment_type);
        $this->assertSame($newEnd, $amendment->changes['valid_to']['to']);
        $this->assertSame(['from' => 'expired', 'to' => 'active'], $amendment->changes['status']);
    }

    public function test_extending_an_expired_po_with_no_balance_marks_it_exhausted(): void
    {
        $po = CustomerPurchaseOrder::factory()->blanket()->create();
        $this->allocation->addLine($po, $this->lineAttributes(5));
        $this->commit($po, 5);
        $po->refresh()->forceFill([
            'status' => PurchaseOrderStatus::Expired,
            'valid_to' => now()->subDay()->toDateString(),
        ])->save();

        $updated = $this->registry->changeValidity($po, null, now()->addMonth()->toDateString(), 'Renewed');

        $this->assertSame(PurchaseOrderStatus::Exhausted, $updated->status);
    }

    public function test_unchanged_or_inverted_validity_is_rejected(): void
    {
        [$po] = $this->blanketWithLine(10);

        $this->assertValidationError('valid_to', fn () => $this->registry->changeValidity($po, null, $po->valid_to->toDateString(), 'Same'));
        $this->assertValidationError('valid_to', fn () => $this->registry->changeValidity(
            $po,
            now()->toDateString(),
            now()->subDay()->toDateString(),
            'Backwards',
        ));
    }

    public function test_update_details_returns_null_when_nothing_changed(): void
    {
        [$po, $line] = $this->blanketWithLine(100, notifyAt: 10);

        $result = $this->registry->updateDetails(
            $po,
            [
                'po_number' => $po->po_number,
                'invoicing_mode' => $po->invoicing_mode->value,
                'expiry_notice_days' => $po->expiry_notice_days,
                'notes' => $po->notes,
            ],
            [(string) $line->id => 10],
            null,
            'No-op',
        );

        $this->assertNull($result);
        $this->assertSame(0, CustomerPurchaseOrderAmendment::query()->count());
    }

    public function test_update_details_records_only_changed_fields_and_rearms_threshold_alerts(): void
    {
        [$po, $line] = $this->blanketWithLine(100, notifyAt: 10);
        $line->forceFill(['threshold_notified_at' => now()])->save();

        $amendment = $this->registry->updateDetails(
            $po,
            [
                'po_number' => $po->po_number,
                'invoicing_mode' => 'periodic',
                'invoicing_period' => 'quarterly',
                'expiry_notice_days' => 45,
            ],
            [(string) $line->id => 20],
            null,
            'Finance asked for quarterly billing',
        );

        $this->assertNotNull($amendment);
        $this->assertSame(PurchaseOrderAmendmentType::UpdateDetails, $amendment->amendment_type);
        $this->assertArrayNotHasKey('po_number', $amendment->changes);
        $this->assertSame(['from' => 'per_job', 'to' => 'periodic'], $amendment->changes['invoicing_mode']);
        $this->assertSame(['from' => null, 'to' => 'quarterly'], $amendment->changes['invoicing_period']);
        $this->assertSame(['from' => 30, 'to' => 45], $amendment->changes['expiry_notice_days']);
        $this->assertSame(['from' => 10, 'to' => 20], $amendment->changes['line_1_notify_remaining_qty']);

        $line->refresh();
        $this->assertSame(20, $line->notify_remaining_qty);
        $this->assertNull($line->threshold_notified_at);
    }

    public function test_update_details_rejects_an_alert_level_at_or_above_the_ordered_quantity(): void
    {
        [$po, $line] = $this->blanketWithLine(100);

        $this->assertValidationError("lineThresholds.{$line->id}", fn () => $this->registry->updateDetails(
            $po,
            [],
            [(string) $line->id => 100],
            null,
            'Bad threshold',
        ));
    }

    public function test_update_details_rejects_a_po_number_used_by_another_blanket_po(): void
    {
        $customer = $this->customer();
        $this->registry->createBlanket($this->attributes($customer, ['po_number' => 'PO-1']), [$this->lineInput(10)]);
        $second = $this->registry->createBlanket($this->attributes($customer, ['po_number' => 'PO-2']), [$this->lineInput(10)]);

        $this->assertValidationError('po_number', fn () => $this->registry->updateDetails($second, ['po_number' => 'PO-1'], [], null, 'Rename'));
    }

    public function test_replacing_the_file_stores_the_new_one_and_deletes_the_old_one(): void
    {
        $customer = $this->customer();
        $po = $this->registry->createBlanket(
            $this->attributes($customer),
            [$this->lineInput(10)],
            UploadedFile::fake()->create('original.pdf', 10, 'application/pdf'),
        );
        $originalPath = $po->file_path;

        $amendment = $this->registry->updateDetails(
            $po,
            [],
            [],
            UploadedFile::fake()->create('signed.pdf', 10, 'application/pdf'),
            'Signed copy received',
        );

        $po->refresh();
        $this->assertSame(['from' => 'original.pdf', 'to' => 'signed.pdf'], $amendment->changes['file']);
        $this->assertSame('signed.pdf', $po->file_name);
        Storage::disk('public')->assertExists($po->file_path);
        Storage::disk('public')->assertMissing($originalPath);
    }

    public function test_closing_releases_open_reservations_but_keeps_committed_cover(): void
    {
        [$po, $line] = $this->blanketWithLine(100);
        $this->allocation->reserve($po, [$this->demand(20)], (string) Str::uuid());
        $this->allocation->reserve($po->fresh(), [$this->demand(5)], (string) Str::uuid());
        $this->commit($po, 30);

        $closed = $this->registry->close($po, 'Contract ended early');

        $this->assertSame(PurchaseOrderStatus::Closed, $closed->status);
        $this->assertNotNull($closed->closed_at);

        $line->refresh();
        $this->assertSame(0, $line->reserved_qty);
        $this->assertSame(30, $line->committed_qty);

        $amendment = CustomerPurchaseOrderAmendment::query()->where('customer_purchase_order_id', $po->id)->sole();
        $this->assertSame(PurchaseOrderAmendmentType::Close, $amendment->amendment_type);
        $this->assertSame(25, $amendment->changes['reservations_released']);
        $this->assertSame(['from' => 'active', 'to' => 'closed'], $amendment->changes['status']);
    }

    public function test_a_po_with_committed_samples_cannot_be_cancelled(): void
    {
        [$po] = $this->blanketWithLine(100);
        $this->commit($po, 1);

        $this->assertValidationError('reason', fn () => $this->registry->cancel($po, 'Mistake'));

        $this->assertSame(PurchaseOrderStatus::Active, $po->fresh()->status);
    }

    public function test_cancelling_an_unused_po_releases_its_reservations(): void
    {
        [$po, $line] = $this->blanketWithLine(100);
        $this->allocation->reserve($po, [$this->demand(15)], (string) Str::uuid());

        $cancelled = $this->registry->cancel($po, 'Captured twice');

        $this->assertSame(PurchaseOrderStatus::Cancelled, $cancelled->status);
        $this->assertSame(0, $line->fresh()->reserved_qty);
        $this->assertSame(100, $line->fresh()->remaining_qty);
    }

    public function test_a_closed_or_cancelled_po_can_no_longer_be_amended(): void
    {
        [$po, $line] = $this->blanketWithLine(100);
        $this->registry->close($po, 'Done');

        $this->assertValidationError('reason', fn () => $this->registry->adjustLineQuantity($line, 10, 'Top up'));
        $this->assertValidationError('reason', fn () => $this->registry->addLine($po, $this->lineInput(5), 'More'));
        $this->assertValidationError('reason', fn () => $this->registry->changeValidity($po, null, now()->addYear()->toDateString(), 'Extend'));
        $this->assertValidationError('reason', fn () => $this->registry->updateDetails($po, ['notes' => 'x'], [], null, 'Edit'));
        $this->assertValidationError('reason', fn () => $this->registry->close($po, 'Again'));
        $this->assertValidationError('reason', fn () => $this->registry->cancel($po, 'Cancel'));

        $this->assertSame(1, CustomerPurchaseOrderAmendment::query()->where('customer_purchase_order_id', $po->id)->count());
    }

    public function test_release_all_reservations_only_returns_open_holds(): void
    {
        [$po, $line] = $this->blanketWithLine(100);
        $released = (string) Str::uuid();
        $open = (string) Str::uuid();
        $this->allocation->reserve($po, [$this->demand(10)], $released);
        $this->allocation->releaseReservation($po->fresh(), $released, 'Enquiry cancelled');
        $this->allocation->reserve($po->fresh(), [$this->demand(12)], $open);

        $quantity = $this->allocation->releaseAllReservations($po->fresh(), 'PO closed');

        $this->assertSame(12, $quantity);
        $this->assertSame(0, $line->fresh()->reserved_qty);
        $this->assertSame(0, $this->allocation->releaseAllReservations($po->fresh(), 'Nothing left'));
    }

    private function customer(): CRMCustomer
    {
        return CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer '.Str::random(6),
            'code' => strtoupper(Str::random(6)),
        ]);
    }

    private function quotationFor(CRMCustomer $customer): QuotationHeader
    {
        return QuotationHeader::query()->create([
            'crm_customer_id' => (string) $customer->id,
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(30)->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function attributes(CRMCustomer $customer, array $overrides = []): array
    {
        return array_merge([
            'po_number' => 'PO-'.Str::upper(Str::random(8)),
            'customer_id' => (string) $customer->id,
            'quotation_header_id' => null,
            'currency_id' => null,
            'valid_from' => now()->subDay()->toDateString(),
            'valid_to' => now()->addYear()->toDateString(),
            'invoicing_mode' => PurchaseOrderInvoicingMode::PerJob->value,
            'invoicing_period' => null,
            'expiry_notice_days' => 30,
            'notes' => null,
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    private function lineInput(int $orderedQty, ?int $notifyAt = null, string $description = 'Potable water package'): array
    {
        return [
            'description' => $description,
            'ordered_qty' => $orderedQty,
            'unit_price_gross' => '157.50',
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$this->packageId],
            'is_package' => true,
            'quotation_detail_id' => null,
            'notify_remaining_qty' => $notifyAt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function lineAttributes(int $orderedQty): array
    {
        return [
            'description' => 'Potable water package',
            'ordered_qty' => $orderedQty,
            'unit_price_gross' => 157.50,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$this->packageId],
            'is_package' => true,
        ];
    }

    /**
     * @return array{0: CustomerPurchaseOrder, 1: CustomerPurchaseOrderLine}
     */
    private function blanketWithLine(int $orderedQty, ?int $notifyAt = null): array
    {
        $po = $this->registry->createBlanket($this->attributes($this->customer()), [$this->lineInput($orderedQty, $notifyAt)]);

        return [$po, $po->lines->first()];
    }

    private function demand(int $quantity): PurchaseOrderDemandItem
    {
        return new PurchaseOrderDemandItem('package', $this->sampleTypeId, [$this->packageId], $quantity);
    }

    private function commit(CustomerPurchaseOrder $po, int $quantity): void
    {
        $this->allocation->commit($po->fresh(), [$this->demand($quantity)], (string) Str::uuid(), null, now());
    }

    private function assertValidationError(string $key, callable $callback): void
    {
        try {
            $callback();
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($key, $exception->errors());

            return;
        }

        $this->fail("Expected a validation error on [{$key}].");
    }
}
