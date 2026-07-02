<?php

namespace Tests\Feature\SampleWorkflow;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnquiryQuoteInvoiceLinkageTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_accepted_quotation_prefers_accepted_header_id(): void
    {
        $customerId = (string) Str::uuid();
        $accepted = QuotationHeader::query()->create([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customerId,
            'quote_number' => 'AMSQ260702-011',
            'quote_date' => now()->toDateString(),
            'status' => 'Quote Complete',
        ]);
        $current = QuotationHeader::query()->create([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customerId,
            'quote_number' => 'AMSQ260702-012',
            'quote_date' => now()->toDateString(),
            'status' => 'Quote Complete',
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            'accepted_quotation_header_id' => $accepted->id,
            'current_quotation_header_id' => $current->id,
        ]);

        $resolved = app(EnquiryReceptionReadinessService::class)->resolveAcceptedQuotation($enquiry);

        $this->assertNotNull($resolved);
        $this->assertSame((string) $accepted->id, (string) $resolved->id);
    }
}
