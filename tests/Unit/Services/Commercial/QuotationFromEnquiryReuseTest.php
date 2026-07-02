<?php

namespace Tests\Unit\Services\Commercial;

use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuotationFromEnquiryReuseTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Commercial User',
            'email' => 'commercial.reuse@example.test',
            'password' => bcrypt('password'),
            'active' => 1,
            'is_client' => 0,
        ]);
    }

    public function test_list_reusable_customer_quotations_returns_completed_non_expired_quotes(): void
    {
        $customerId = (string) Str::uuid();

        $reusable = $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'expiring_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->createQuotation($customerId, [
            'status' => 'Quote In Preparation',
            'expiring_date' => now()->addDays(10)->toDateString(),
        ]);

        $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'expiring_date' => now()->subDay()->toDateString(),
        ]);

        $results = app(QuotationFromEnquiryService::class)
            ->listReusableCustomerQuotations($customerId);

        $this->assertCount(1, $results);
        $this->assertSame((string) $reusable->id, (string) $results->first()->id);
    }

    public function test_build_inline_lines_expands_comma_separated_analysis_types(): void
    {
        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeA = (string) Str::uuid();
        $analysisTypeB = (string) Str::uuid();

        $header = $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'quotation_type' => 'Analysis',
        ]);

        QuotationDetails::query()->create([
            'id' => (string) Str::uuid(),
            'quotation_header_id' => $header->id,
            'sample_type' => $sampleTypeId,
            'quantity' => 2,
            'unit_price' => 200,
            'tax' => 15,
            'part_no' => $analysisTypeA.','.$analysisTypeB,
            'description' => 'Bundle',
        ]);

        AnalysisType::query()->create([
            'id' => $analysisTypeA,
            'name' => 'Analysis A',
            'code' => 'ANA',
            'sample_type_id' => $sampleTypeId,
            'lab_id' => (string) Str::uuid(),
            'company_id' => (string) Str::uuid(),
            'active' => 1,
        ]);

        AnalysisType::query()->create([
            'id' => $analysisTypeB,
            'name' => 'Analysis B',
            'code' => 'ANB',
            'sample_type_id' => $sampleTypeId,
            'lab_id' => (string) Str::uuid(),
            'company_id' => (string) Str::uuid(),
            'active' => 1,
        ]);

        $lines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromQuotationHeader($header->fresh());

        $this->assertCount(2, $lines);
        $this->assertSame($analysisTypeA, $lines[0]['analysis_type_id']);
        $this->assertSame($analysisTypeB, $lines[1]['analysis_type_id']);
        $this->assertSame(100.0, (float) $lines[0]['unit_price']);
        $this->assertSame(100.0, (float) $lines[1]['unit_price']);
        $this->assertSame(15.0, (float) $lines[0]['tax']);
    }

    public function test_link_existing_quotation_reuses_shared_quote_without_cloning(): void
    {
        $customerId = (string) Str::uuid();
        $source = $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'expiring_date' => now()->addDays(14)->toDateString(),
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'walk_in',
        ]);

        $updated = app(QuotationFromEnquiryService::class)
            ->linkExistingQuotationToEnquiry($enquiry, $source);

        $this->assertSame((string) $source->id, (string) $updated->current_quotation_header_id);
        $this->assertSame((string) $source->id, (string) $updated->selected_source_quotation_header_id);
        $this->assertSame(
            SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING,
            $updated->quotation_source_mode
        );
        $this->assertTrue($updated->usesSharedSourceQuotation());
        $this->assertSame(1, QuotationHeader::query()->where('crm_customer_id', $customerId)->count());
    }

    public function test_ensure_editable_quotation_clones_shared_source_on_first_edit(): void
    {
        $this->actingAs($this->user);

        $customerId = (string) Str::uuid();
        $source = $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'expiring_date' => now()->addDays(14)->toDateString(),
        ]);

        QuotationDetails::query()->create([
            'id' => (string) Str::uuid(),
            'quotation_header_id' => $source->id,
            'sample_type' => (string) Str::uuid(),
            'quantity' => 1,
            'unit_price' => 50,
            'tax' => 10,
            'part_no' => (string) Str::uuid(),
            'description' => 'Line 1',
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'portal',
            'selected_source_quotation_header_id' => $source->id,
            'current_quotation_header_id' => $source->id,
            'quotation_source_mode' => SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING,
        ]);

        $service = app(QuotationFromEnquiryService::class);
        $editable = $service->ensureEditableQuotationForEnquiry($enquiry);
        $enquiry->refresh();

        $this->assertNotSame((string) $source->id, (string) $editable->id);
        $this->assertSame((string) $source->id, (string) $editable->source_quotation_header_id);
        $this->assertSame((string) $editable->id, (string) $enquiry->current_quotation_header_id);
        $this->assertSame((string) $source->id, (string) $enquiry->selected_source_quotation_header_id);
        $this->assertFalse($enquiry->usesSharedSourceQuotation());
        $this->assertSame(50.0, (float) $editable->details->first()->unit_price);
        $this->assertSame(50.0, (float) $source->fresh()->details->first()->unit_price);
    }

    public function test_create_or_open_reuses_linked_source_quotation_without_duplicating(): void
    {
        $customerId = (string) Str::uuid();
        $source = $this->createQuotation($customerId, [
            'status' => 'Quote Complete',
            'expiring_date' => now()->addDays(14)->toDateString(),
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'portal',
            'selected_source_quotation_header_id' => $source->id,
            'quotation_source_mode' => SampleSubmissionRequest::QUOTATION_SOURCE_FROM_EXISTING,
        ]);

        $header = app(QuotationFromEnquiryService::class)->createOrOpen($enquiry);

        $this->assertSame((string) $source->id, (string) $header->id);
        $this->assertSame(1, QuotationHeader::query()->where('crm_customer_id', $customerId)->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createQuotation(string $customerId, array $overrides = []): QuotationHeader
    {
        return QuotationHeader::query()->create(array_merge([
            'id' => (string) Str::uuid(),
            'crm_customer_id' => $customerId,
            'quote_number' => 'AMSQ'.Str::upper(Str::random(6)),
            'quote_date' => now()->toDateString(),
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'is_complete' => 1,
            'is_approved' => 1,
        ], $overrides));
    }
}
