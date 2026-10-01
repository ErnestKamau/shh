<?php

namespace Tests\Feature\Commercial;

use App\Enums\Commercial\PurchaseOrderStatus;
use App\Enums\Commercial\PurchaseOrderType;
use App\Events\Commercial\PurchaseOrderLineExhausted;
use App\Events\Commercial\PurchaseOrderLineThresholdReached;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\Commercial\CustomerPurchaseOrderLedgerEntry;
use App\Models\Commercial\CustomerPurchaseOrderLine;
use App\Models\Commercial\EnquiryPurchaseOrderChange;
use App\Models\CRM\CRMCustomer;
use App\Models\EnquiryQuotation;
use App\Models\SampleSubmissionRequest;
use App\Models\System\SystemConfiguration;
use App\QuotationHeader;
use App\Services\Commercial\EnquiryPurchaseOrderService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Commercial\PurchaseOrderAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EnquiryPurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private EnquiryPurchaseOrderService $service;

    private PurchaseOrderAllocationService $allocation;

    private string $sampleTypeId;

    private string $analysisTypeId;

    private int $requestNumber;

    protected function setUp(): void
    {
        parent::setUp();

        config(['purchase_orders.enabled' => true]);
        Event::fake([PurchaseOrderLineExhausted::class, PurchaseOrderLineThresholdReached::class]);

        $this->service = app(EnquiryPurchaseOrderService::class);
        $this->allocation = app(PurchaseOrderAllocationService::class);
        $this->sampleTypeId = (string) Str::uuid();
        $this->analysisTypeId = (string) Str::uuid();
        $this->requestNumber = random_int(100000, 800000);
    }

    public function test_walk_in_customers_may_skip_the_po(): void
    {
        $enquiry = $this->enquiryFor($this->customer(), 5);

        $this->assertSame(EnquiryPurchaseOrderService::REQUIREMENT_SKIPPABLE, $this->service->requirement($enquiry));
    }

    public function test_credit_customers_require_po_cover(): void
    {
        $enquiry = $this->enquiryFor($this->customer('credit'), 5);

        $this->assertSame(EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED, $this->service->requirement($enquiry));
    }

    public function test_advance_customers_have_an_optional_po(): void
    {
        $enquiry = $this->enquiryFor($this->customer('advance'), 5);

        $this->assertSame(EnquiryPurchaseOrderService::REQUIREMENT_OPTIONAL, $this->service->requirement($enquiry));
    }

    public function test_a_blanket_is_auto_selected_only_when_exactly_one_has_balance(): void
    {
        $customer = $this->customer();
        [$withBalance] = $this->blanketFor($customer, 10);
        [$usedUp] = $this->blanketFor($customer, 10);
        $this->allocation->commit($usedUp->fresh(), [$this->demandItem(10)], (string) Str::uuid(), null, now());

        $this->assertSame((string) $withBalance->id, (string) $this->service->autoSelectBlanket((string) $customer->id)?->id);

        $this->blanketFor($customer, 5);

        $this->assertNull($this->service->autoSelectBlanket((string) $customer->id));
    }

    public function test_expired_closed_and_single_pos_are_not_offered_as_blankets(): void
    {
        $customer = $this->customer();
        $this->blanketFor($customer, 10, ['valid_from' => now()->subYear()->toDateString(), 'valid_to' => now()->subDay()->toDateString()]);
        $this->blanketFor($customer, 10, ['status' => PurchaseOrderStatus::Closed]);
        CustomerPurchaseOrder::factory()->create(['customer_id' => (string) $customer->id]);

        $this->assertTrue($this->service->blanketOrdersFor((string) $customer->id)->isEmpty());
        $this->assertNull($this->service->autoSelectBlanket((string) $customer->id));
        $this->assertSame([], $this->service->customerIdsWithBlanketOrders([(string) $customer->id]));
    }

    public function test_customers_with_a_drawable_blanket_are_listed(): void
    {
        $withBlanket = $this->customer();
        $without = $this->customer();
        $this->blanketFor($withBlanket, 10);

        $ids = $this->service->customerIdsWithBlanketOrders();

        $this->assertArrayHasKey((string) $withBlanket->id, $ids);
        $this->assertArrayNotHasKey((string) $without->id, $ids);
        $this->assertSame([], $this->service->customerIdsWithBlanketOrders([]));
    }

    public function test_capturing_a_blanket_binds_it_and_links_its_quotation(): void
    {
        $customer = $this->customer();
        $quotation = $this->quotationFor($customer);
        [$po] = $this->blanketFor($customer, 50, ['quotation_header_id' => (string) $quotation->id]);
        $enquiry = $this->enquiryFor($customer, 5, SampleSubmissionRequest::STATUS_REQUESTED);

        $captured = $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $this->assertSame((string) $po->id, (string) $captured->customer_purchase_order_id);
        $this->assertSame($po->po_number, $captured->client_po_number);
        $this->assertFalse((bool) $captured->po_skipped);
        $this->assertSame((string) $quotation->id, (string) $captured->accepted_quotation_header_id);
        $this->assertSame(SampleSubmissionRequest::STATUS_REQUESTED, $captured->status);
        $this->assertTrue(EnquiryQuotation::query()
            ->where('sample_submission_request_id', (string) $enquiry->id)
            ->where('quotation_header_id', (string) $quotation->id)
            ->where('link_source', EnquiryQuotation::LINK_SOURCE_PURCHASE_ORDER)
            ->exists());
    }

    public function test_a_blanket_from_another_customer_is_rejected(): void
    {
        [$po] = $this->blanketFor($this->customer(), 50);
        $enquiry = $this->enquiryFor($this->customer(), 5);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]));
    }

    public function test_an_expired_blanket_cannot_be_captured(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 50, ['valid_from' => now()->subYear()->toDateString(), 'valid_to' => now()->subDay()->toDateString()]);
        $enquiry = $this->enquiryFor($customer, 5);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]));
    }

    public function test_a_single_po_cannot_be_drawn_as_a_blanket(): void
    {
        $customer = $this->customer();
        $po = CustomerPurchaseOrder::factory()->create(['customer_id' => (string) $customer->id]);
        $enquiry = $this->enquiryFor($customer, 5);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]));
    }

    public function test_a_single_po_number_creates_and_binds_a_po_for_the_enquiry(): void
    {
        $customer = $this->customer();
        $enquiry = $this->enquiryFor($customer, 5);

        $captured = $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_SINGLE,
            'client_po_number' => 'PO-4471',
        ]);

        $po = CustomerPurchaseOrder::query()->findOrFail((string) $captured->customer_purchase_order_id);
        $this->assertSame('PO-4471', $captured->client_po_number);
        $this->assertSame('PO-4471', $po->po_number);
        $this->assertSame(PurchaseOrderType::Single, $po->po_type);
    }

    public function test_credit_customers_cannot_skip_the_po(): void
    {
        $enquiry = $this->enquiryFor($this->customer('credit'), 5);

        $this->assertValidationError('po_skipped', fn () => $this->service->capture($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_NONE,
            'po_skipped' => true,
        ]));
    }

    public function test_credit_customers_can_continue_with_the_po_to_follow(): void
    {
        $enquiry = $this->enquiryFor($this->customer('credit'), 5);

        $captured = $this->service->capture($enquiry, ['mode' => EnquiryPurchaseOrderService::MODE_SINGLE, 'client_po_number' => '  ']);

        $this->assertNull($captured->customer_purchase_order_id);
        $this->assertNull($captured->client_po_number);
        $this->assertFalse((bool) $captured->po_skipped);
    }

    public function test_walk_in_customers_without_a_po_are_marked_skipped(): void
    {
        $enquiry = $this->enquiryFor($this->customer(), 5);

        $captured = $this->service->capture($enquiry, ['mode' => EnquiryPurchaseOrderService::MODE_NONE]);

        $this->assertTrue((bool) $captured->po_skipped);
        $this->assertNull($captured->customer_purchase_order_id);
    }

    public function test_a_walk_in_enquiry_taken_off_a_blanket_records_a_clean_skip(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 40);
        $enquiry = $this->service->capture($this->enquiryFor($customer, 5), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $captured = $this->service->capture($enquiry, ['mode' => EnquiryPurchaseOrderService::MODE_NONE]);

        $skipRow = CustomerPurchaseOrder::query()->where('enquiry_id', (string) $enquiry->id)->firstOrFail();
        $this->assertNull($captured->client_po_number);
        $this->assertTrue((bool) $captured->po_skipped);
        $this->assertNull($skipRow->po_number);
        $this->assertTrue((bool) $skipRow->po_skipped);
        $this->assertSame($po->po_number, $po->fresh()->po_number);
    }

    public function test_capture_and_mark_ready_reserves_the_requested_samples(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 40);
        $enquiry = $this->enquiryFor($customer, 6);

        $ready = $this->service->captureAndMarkReady($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $ready->status);
        $this->assertSame($po->po_number, $ready->client_po_number);
        $this->assertSame(6, $line->fresh()->reserved_qty);
        $this->assertSame(34, $line->fresh()->remaining_qty);
    }

    public function test_a_reservation_only_takes_what_is_left_and_never_blocks_reception(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 4);
        $enquiry = $this->enquiryFor($customer, 10);

        $ready = $this->service->captureAndMarkReady($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $ready->status);
        $this->assertSame(4, $line->fresh()->reserved_qty);
        $this->assertSame(0, $line->fresh()->remaining_qty);
    }

    public function test_using_a_blanket_po_skips_the_quotation_step(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 40);
        $enquiry = $this->enquiryFor($customer, 3, SampleSubmissionRequest::STATUS_REQUESTED);

        $this->assertTrue($this->service->canUseBlanketShortcut($enquiry));

        $ready = $this->service->useBlanketPurchaseOrder($enquiry, (string) $po->id);

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $ready->status);
        $this->assertSame((string) $po->id, (string) $ready->customer_purchase_order_id);
        $this->assertSame(3, $line->fresh()->reserved_qty);
    }

    public function test_the_blanket_shortcut_is_refused_once_a_quotation_was_accepted(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 40);
        $enquiry = $this->enquiryFor($customer, 3, SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED);

        $this->assertFalse($this->service->canUseBlanketShortcut($enquiry));
        $this->assertValidationError('customer_purchase_order_id', fn () => $this->service->useBlanketPurchaseOrder($enquiry, (string) $po->id));
    }

    public function test_the_blanket_shortcut_needs_a_blanket_po(): void
    {
        $enquiry = $this->enquiryFor($this->customer(), 3, SampleSubmissionRequest::STATUS_REQUESTED);

        $this->assertFalse($this->service->canUseBlanketShortcut($enquiry));
    }

    public function test_changing_the_po_at_reception_moves_the_reservation_and_is_audited(): void
    {
        $customer = $this->customer();
        [$from, $fromLine] = $this->blanketFor($customer, 40);
        [$to, $toLine] = $this->blanketFor($customer, 40);
        $enquiry = $this->service->captureAndMarkReady($this->enquiryFor($customer, 6), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $from->id,
        ]);

        $change = $this->service->changeAtReception($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $to->id,
        ], null, 'Customer moved this job to the new annual PO');

        $this->assertSame(0, $fromLine->fresh()->reserved_qty);
        $this->assertSame(40, $fromLine->fresh()->remaining_qty);
        $this->assertSame(6, $toLine->fresh()->reserved_qty);
        $this->assertSame((string) $to->id, (string) $enquiry->fresh()->customer_purchase_order_id);

        $this->assertInstanceOf(EnquiryPurchaseOrderChange::class, $change);
        $this->assertSame((string) $from->id, (string) $change->from_customer_purchase_order_id);
        $this->assertSame((string) $to->id, (string) $change->to_customer_purchase_order_id);
        $this->assertSame($from->po_number, $change->from_po_number);
        $this->assertSame($to->po_number, $change->to_po_number);
        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $change->enquiry_status);
        $this->assertSame('Customer moved this job to the new annual PO', $change->reason);
    }

    public function test_removing_the_po_at_reception_releases_the_reservation(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 40);
        $enquiry = $this->service->captureAndMarkReady($this->enquiryFor($customer, 6), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $change = $this->service->changeAtReception($enquiry, ['mode' => EnquiryPurchaseOrderService::MODE_NONE], null, 'PO withdrawn');

        $this->assertSame(0, $line->fresh()->reserved_qty);
        $this->assertNull($enquiry->fresh()->customer_purchase_order_id);
        $this->assertNull($change->to_customer_purchase_order_id);
    }

    public function test_changing_to_the_same_po_is_rejected(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 40);
        $enquiry = $this->service->captureAndMarkReady($this->enquiryFor($customer, 6), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $this->assertValidationError('customer_purchase_order_id', fn () => $this->service->changeAtReception($enquiry, [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ], null, 'No change'));
        $this->assertSame(0, EnquiryPurchaseOrderChange::query()->count());
    }

    public function test_the_po_cannot_be_changed_before_reception_or_after_acceptance(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 40);
        $payload = ['mode' => EnquiryPurchaseOrderService::MODE_BLANKET, 'customer_purchase_order_id' => (string) $po->id];

        foreach ([SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, SampleSubmissionRequest::STATUS_ACCEPTED] as $status) {
            $enquiry = $this->enquiryFor($customer, 6, $status);

            $this->assertFalse($this->service->canChangeAtReception($enquiry));
            $this->assertValidationError('reason', fn () => $this->service->changeAtReception($enquiry, $payload, null, 'Wrong stage'));
        }

        $this->assertSame(0, EnquiryPurchaseOrderChange::query()->count());
    }

    public function test_the_po_cannot_be_changed_once_the_job_exists(): void
    {
        $enquiry = $this->enquiryFor($this->customer(), 6, SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK);
        $this->assertTrue($this->service->canChangeAtReception($enquiry));

        $enquiry->sample_header_id = (string) Str::uuid();

        $this->assertFalse($this->service->canChangeAtReception($enquiry));
    }

    public function test_the_po_cannot_be_changed_when_the_feature_is_disabled(): void
    {
        $enquiry = $this->enquiryFor($this->customer(), 6, SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION);
        config(['purchase_orders.enabled' => false]);

        $this->assertFalse($this->service->canChangeAtReception($enquiry));
    }

    public function test_rejecting_the_samples_releases_the_reservation(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 40);
        $enquiry = $this->service->captureAndMarkReady($this->enquiryFor($customer, 6), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);

        $this->service->releaseReservation($enquiry, 'Samples rejected');

        $this->assertSame(0, $line->fresh()->reserved_qty);
        $this->assertSame(40, $line->fresh()->remaining_qty);
    }

    public function test_no_reservation_is_made_when_the_feature_is_disabled(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 40);
        $enquiry = $this->enquiryFor($customer, 6, SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, [
            'customer_purchase_order_id' => (string) $po->id,
        ]);
        config(['purchase_orders.enabled' => false]);

        app(EnquiryReceptionReadinessService::class)->markReadyForReception($enquiry);

        $this->assertSame(0, $line->fresh()->reserved_qty);
        $this->assertNull($this->service->syncReservation($enquiry->fresh()));
    }

    public function test_preview_writes_nothing_and_counts_the_enquirys_own_reservation(): void
    {
        $customer = $this->customer();
        [$po, $line] = $this->blanketFor($customer, 10);
        $enquiry = $this->service->captureAndMarkReady($this->enquiryFor($customer, 8), [
            'mode' => EnquiryPurchaseOrderService::MODE_BLANKET,
            'customer_purchase_order_id' => (string) $po->id,
        ]);
        $entries = CustomerPurchaseOrderLedgerEntry::query()->count();

        $own = $this->service->previewFor($enquiry, $po->fresh());
        $other = $this->service->previewFor($this->enquiryFor($customer, 8), $po->fresh());

        $this->assertSame(8, $own['covered']);
        $this->assertSame(0, $own['uncovered']);
        $this->assertSame(8, $own['reserved']);
        $this->assertSame(2, $other['covered']);
        $this->assertSame(6, $other['uncovered']);
        $this->assertSame($entries, CustomerPurchaseOrderLedgerEntry::query()->count());
        $this->assertSame(8, $line->fresh()->reserved_qty);
    }

    public function test_coverage_explains_samples_that_are_not_covered(): void
    {
        $customer = $this->customer();
        [$po] = $this->blanketFor($customer, 40);
        $enquiry = $this->enquiryFor($customer, 4, SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, [
            'sample_lines' => [
                ['sort_order' => 1, 'sample_type_id' => $this->sampleTypeId, 'analysis_type_id' => $this->analysisTypeId, 'analysis_element_id' => null, 'number_of_samples' => 4],
                ['sort_order' => 2, 'sample_type_id' => $this->sampleTypeId, 'analysis_type_id' => (string) Str::uuid(), 'analysis_element_id' => null, 'number_of_samples' => 3],
            ],
            'number_of_samples' => 7,
        ]);

        $coverage = $this->service->previewFor($enquiry, $po);

        $this->assertSame(7, $coverage['requested']);
        $this->assertSame(4, $coverage['covered']);
        $this->assertSame(3, $coverage['uncovered']);
        $this->assertNull($coverage['rows'][0]['reason']);
        $this->assertSame('No line on the PO for this sample type / analysis.', $coverage['rows'][1]['reason']);
    }

    public function test_coverage_without_a_po_covers_nothing(): void
    {
        $coverage = $this->service->coverage($this->enquiryFor($this->customer('credit'), 5));

        $this->assertNull($coverage['purchase_order']);
        $this->assertSame(EnquiryPurchaseOrderService::REQUIREMENT_REQUIRED, $coverage['requirement']);
        $this->assertSame(5, $coverage['requested']);
        $this->assertSame(0, $coverage['covered']);
        $this->assertSame('No PO on this enquiry.', $coverage['rows'][0]['reason']);
    }

    /**
     * @param  'walk_in'|'credit'|'advance'  $billingType
     */
    private function customer(string $billingType = 'walk_in'): CRMCustomer
    {
        $accountStatus = null;
        if ($billingType !== 'walk_in') {
            $accountStatus = (string) SystemConfiguration::query()->create([
                'key' => 'account_status_'.$billingType.'_'.Str::random(4),
                'value' => ucfirst($billingType).' account',
                'meta' => ['billing_type' => $billingType],
                'status' => true,
            ])->id;
        }

        return CRMCustomer::query()->create([
            'id' => (string) Str::uuid7(),
            'name' => 'Customer '.Str::random(6),
            'code' => strtoupper(Str::random(6)),
            'account_status' => $accountStatus,
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
     * @param  array<string, mixed>  $attributes
     * @return array{0: CustomerPurchaseOrder, 1: CustomerPurchaseOrderLine}
     */
    private function blanketFor(CRMCustomer $customer, int $orderedQty, array $attributes = []): array
    {
        $po = CustomerPurchaseOrder::factory()->blanket()->create(array_merge([
            'customer_id' => (string) $customer->id,
        ], $attributes));

        $line = $this->allocation->addLine($po, [
            'description' => 'Potable water metals',
            'ordered_qty' => $orderedQty,
            'unit_price_gross' => 157.50,
            'sample_type_id' => $this->sampleTypeId,
            'analysis_type_ids' => [$this->analysisTypeId],
            'is_package' => false,
        ]);

        return [$po->fresh(), $line];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function enquiryFor(
        CRMCustomer $customer,
        int $samples,
        string $status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
        array $attributes = [],
    ): SampleSubmissionRequest {
        return SampleSubmissionRequest::query()->create(array_merge([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => (string) $customer->id,
            'status' => $status,
            'request_number' => $this->requestNumber++,
            'number_of_samples' => $samples,
            'sample_lines' => [
                ['sort_order' => 1, 'sample_type_id' => $this->sampleTypeId, 'analysis_type_id' => $this->analysisTypeId, 'analysis_element_id' => null, 'number_of_samples' => $samples],
            ],
        ], $attributes))->fresh(['customer']);
    }

    private function demandItem(int $quantity): \App\DTOs\Commercial\PurchaseOrderDemandItem
    {
        return new \App\DTOs\Commercial\PurchaseOrderDemandItem(
            \App\Services\Commercial\PurchaseOrderDemandBuilder::keyFor($this->sampleTypeId, $this->analysisTypeId),
            $this->sampleTypeId,
            [$this->analysisTypeId],
            $quantity,
        );
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
