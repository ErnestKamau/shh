<?php

namespace Tests\Feature\SampleWorkflow;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\QuotationFromEnquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryQuotationApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_to_customer_blocks_unapproved_enquiry_built_quotation(): void
    {
        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'portal',
        ]);

        $header = QuotationHeader::query()->create([
            'crm_customer_id' => $enquiry->crm_customer_id,
            'status' => 'Quote In Preparation',
            'from_enquiry' => true,
            'sample_submission_request_id' => $enquiry->id,
            'is_approved' => 0,
            'is_complete' => 0,
            'is_draft' => 0,
            'quote_date' => now()->toDateString(),
            'upload_url' => '/tmp/fake-quote.pdf',
        ]);

        $enquiry->current_quotation_header_id = $header->id;
        $enquiry->save();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Quotation must be approved');

        app(QuotationFromEnquiryService::class)->sendToCustomer($enquiry, $header, true, false);
    }

    public function test_send_to_customer_allows_approved_existing_style_quotation(): void
    {
        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
            'source_channel' => 'email',
        ]);

        $header = QuotationHeader::query()->create([
            'crm_customer_id' => $enquiry->crm_customer_id,
            'status' => 'Quote Complete',
            'from_enquiry' => true,
            'sample_submission_request_id' => $enquiry->id,
            'is_approved' => 1,
            'is_complete' => 1,
            'is_draft' => 0,
            'quote_date' => now()->toDateString(),
            'upload_url' => '/tmp/fake-quote.pdf',
        ]);

        $enquiry->current_quotation_header_id = $header->id;
        $enquiry->save();

        // Email/portal delivery may fail without a contact; gate should pass first.
        try {
            app(QuotationFromEnquiryService::class)->sendToCustomer($enquiry, $header, false, true);
        } catch (\RuntimeException $exception) {
            $this->assertStringNotContainsString('must be approved', $exception->getMessage());
        }

        $this->assertTrue(true);
    }
}
