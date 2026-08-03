<?php

namespace Tests\Unit\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Commercial\QuotationFromEnquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class QuotationFromEnquiryExistingQuotationTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligible_quotations_for_customer_requires_complete_and_unexpired(): void
    {
        $customerId = (string) Str::uuid();
        $otherCustomerId = (string) Str::uuid();

        $valid = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'total_amount' => 100,
            'is_draft' => 0,
        ]);

        QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'status' => 'Quote In Preparation',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'is_draft' => 0,
        ]);

        QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->subDay()->toDateString(),
            'is_draft' => 0,
        ]);

        QuotationHeader::query()->create([
            'crm_customer_id' => $otherCustomerId,
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'is_draft' => 0,
        ]);

        $eligible = app(QuotationFromEnquiryService::class)
            ->eligibleQuotationsForCustomer($customerId);

        $this->assertCount(1, $eligible);
        $this->assertSame((string) $valid->id, (string) $eligible->first()->id);
    }

    public function test_attach_existing_quotation_links_enquiry_and_header(): void
    {
        $customerId = (string) Str::uuid();

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'walk_in',
        ]);

        $header = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(5)->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        $attached = app(QuotationFromEnquiryService::class)
            ->attachExistingQuotation($enquiry, $header);

        $enquiry->refresh();

        $this->assertSame((string) $header->id, (string) $enquiry->current_quotation_header_id);
        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS, $enquiry->status);
        $this->assertSame((string) $enquiry->id, (string) $attached->sample_submission_request_id);
    }

    public function test_quotation_mismatch_warnings_when_config_has_extra_parameters(): void
    {
        $header = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(5)->toDateString(),
            'is_draft' => 0,
        ]);

        $quoteElement = (string) Str::uuid();
        $configOnlyElement = (string) Str::uuid();

        QuotationDetails::query()->create([
            'quotation_header_id' => $header->id,
            'quantity' => 1,
            'unit_price' => 50,
            'tax' => 0,
            'default_analytes' => $quoteElement,
            'accredited_analytes' => $quoteElement,
        ]);

        $warnings = app(QuotationFromEnquiryService::class)->quotationMismatchWarnings([
            [
                'parameter_keys' => [$quoteElement, $configOnlyElement],
            ],
        ], $header->fresh(['details']));

        $this->assertNotEmpty($warnings);
        $this->assertStringContainsString('not covered by the selected quotation', $warnings[0]);
    }

    public function test_attach_existing_quotation_cannot_steal_a_quote_from_another_enquiry(): void
    {
        $customerId = (string) Str::uuid();
        $owner = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);
        $secondEnquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'walk_in',
        ]);
        $header = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'sample_submission_request_id' => $owner->id,
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(5)->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already owned by another enquiry');

        app(QuotationFromEnquiryService::class)
            ->attachExistingQuotation($secondEnquiry, $header);
    }
}
