<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\Commercial\CustomerPurchaseOrder;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\CustomerPurchaseOrderService;
use App\Services\Commercial\EnquiryAccountSettingsService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CustomerPurchaseOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_normalize_payload_marks_skip_when_walk_in_and_empty_po(): void
    {
        $enquiry = $this->makeEnquiry();

        $account = Mockery::mock(EnquiryAccountSettingsService::class);
        $account->shouldReceive('poRulesForCustomer')->andReturn([
            'type' => 'walk_in',
            'requires_po' => false,
            'requires_advance_reference' => false,
            'allows_po_skip' => true,
        ]);

        $service = new CustomerPurchaseOrderService(
            $account,
            Mockery::mock(EnquiryReceptionReadinessService::class),
        );

        $this->assertSame(
            ['client_po_number' => null, 'po_skipped' => true],
            $service->normalizePayload($enquiry, ['client_po_number' => '']),
        );
    }

    public function test_normalize_payload_clears_skip_when_number_present(): void
    {
        $enquiry = $this->makeEnquiry();

        $service = new CustomerPurchaseOrderService(
            Mockery::mock(EnquiryAccountSettingsService::class),
            Mockery::mock(EnquiryReceptionReadinessService::class),
        );

        $this->assertSame(
            ['client_po_number' => 'PO-100', 'po_skipped' => false],
            $service->normalizePayload($enquiry, [
                'client_po_number' => ' PO-100 ',
                'po_skipped' => true,
            ]),
        );
    }

    public function test_upsert_syncs_enquiry_and_creates_registry_row(): void
    {
        Storage::fake('public');

        $enquiry = $this->makeEnquiry();

        $account = Mockery::mock(EnquiryAccountSettingsService::class);
        $account->shouldReceive('poRulesForCustomer')->andReturn([
            'type' => 'walk_in',
            'requires_po' => false,
            'requires_advance_reference' => false,
            'allows_po_skip' => true,
        ]);

        $service = new CustomerPurchaseOrderService(
            $account,
            Mockery::mock(EnquiryReceptionReadinessService::class),
        );

        $file = UploadedFile::fake()->create('client-po.pdf', 120, 'application/pdf');
        $uploaderId = (string) Str::uuid();

        $po = $service->upsertForEnquiry(
            $enquiry,
            ['client_po_number' => 'ADNOC-55'],
            $file,
            null,
            $uploaderId,
        );

        $this->assertInstanceOf(CustomerPurchaseOrder::class, $po);
        $this->assertSame('ADNOC-55', $po->po_number);
        $this->assertFalse($po->po_skipped);
        $this->assertTrue($po->hasFile());
        $this->assertSame((string) $enquiry->id, $po->enquiry_id);
        $this->assertSame($uploaderId, $po->uploaded_by);

        $enquiry->refresh();
        $this->assertSame('ADNOC-55', $enquiry->client_po_number);
        $this->assertFalse($enquiry->po_skipped);

        Storage::disk('public')->assertExists($po->file_path);
    }

    public function test_record_and_mark_ready_calls_reception_service(): void
    {
        $enquiry = $this->makeEnquiry();

        $account = Mockery::mock(EnquiryAccountSettingsService::class);
        $account->shouldReceive('poRulesForCustomer')->andReturn([
            'type' => 'walk_in',
            'requires_po' => false,
            'requires_advance_reference' => false,
            'allows_po_skip' => true,
        ]);
        $account->shouldReceive('validateAcceptPayload')->once();

        $readyEnquiry = clone $enquiry;
        $readyEnquiry->status = SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION;

        $reception = Mockery::mock(EnquiryReceptionReadinessService::class);
        $reception->shouldReceive('markReadyForReception')
            ->once()
            ->andReturn($readyEnquiry);

        $service = new CustomerPurchaseOrderService($account, $reception);

        $result = $service->recordAndMarkReadyForReception(
            $enquiry,
            ['client_po_number' => 'PO-9'],
        );

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $result->status);
        $this->assertDatabaseHas('customer_purchase_orders', [
            'enquiry_id' => (string) $enquiry->id,
            'po_number' => 'PO-9',
            'po_skipped' => false,
        ]);
    }

    private function makeEnquiry(): SampleSubmissionRequest
    {
        return SampleSubmissionRequest::query()->create([
            'id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            'source_channel' => 'walk_in',
            'request_number' => random_int(100, 9999),
            'quotation_accepted_at' => now(),
        ]);
    }
}
