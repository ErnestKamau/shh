<?php

namespace Tests\Unit\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Billing\QuotationReportService;
use App\Services\Commercial\QuotationAcceptanceTatService;
use App\Services\Commercial\QuotationFromEnquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class QuotationWalkInAcceptanceSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_record_walk_in_acceptance_requires_customer_signature(): void
    {
        $enquiry = $this->makeSentEnquiry();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Customer signature is required');

        app(QuotationFromEnquiryService::class)->recordWalkInAcceptance($enquiry);
    }

    public function test_record_walk_in_acceptance_persists_signature_on_quotation_header(): void
    {
        $enquiry = $this->makeSentEnquiry();
        $quotation = QuotationHeader::query()->findOrFail($enquiry->current_quotation_header_id);

        $this->mock(QuotationReportService::class, function ($mock) use ($quotation): void {
            $mock->shouldReceive('storePdf')
                ->once()
                ->andReturn($quotation);
        });

        $this->mock(QuotationAcceptanceTatService::class, function ($mock): void {
            $mock->shouldReceive('recalculateCustomerTat')->once();
        });

        $updated = app(QuotationFromEnquiryService::class)->recordWalkInAcceptance(
            $enquiry,
            null,
            false,
            [
                'signature' => 'data:image/png;base64,walkinsign',
                'signer_name' => 'Walk In Client',
            ],
        );

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED, $updated->status);

        $quotation->refresh();
        $this->assertSame('data:image/png;base64,walkinsign', $quotation->customer_acceptance_signature);
        $this->assertSame('Walk In Client', $quotation->customer_acceptance_signer_name);
        $this->assertSame(QuotationHeader::ACCEPTANCE_CHANNEL_WALK_IN, $quotation->customer_acceptance_channel);
        $this->assertNotNull($quotation->customer_acceptance_signed_at);
    }

    private function makeSentEnquiry(): SampleSubmissionRequest
    {
        $enquiry = SampleSubmissionRequest::query()->create([
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_SENT,
            'source_channel' => 'walk_in',
            'crm_customer_id' => (string) \Illuminate\Support\Str::uuid(),
        ]);

        $quotation = QuotationHeader::query()->create([
            'quote_number' => 'Q-ACC-001',
            'crm_customer_id' => $enquiry->crm_customer_id,
            'sample_submission_request_id' => $enquiry->id,
            'from_enquiry' => true,
            'sent_to_customer_at' => Carbon::now()->subHour(),
            'quote_date' => Carbon::now()->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        $enquiry->current_quotation_header_id = $quotation->id;
        $enquiry->save();

        return $enquiry->fresh(['currentQuotation']);
    }
}
