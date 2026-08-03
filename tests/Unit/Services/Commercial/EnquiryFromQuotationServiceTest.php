<?php

namespace Tests\Unit\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\QuotationHeader;
use App\SampleType;
use App\Services\Commercial\CommercialEnquirySyncService;
use App\Services\Commercial\EnquiryFromQuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class EnquiryFromQuotationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_enquiry_with_an_owned_quotation_and_lab_configuration(): void
    {
        [$quotation, $element] = $this->createMappableQuotation();
        $token = (string) Str::uuid();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 2,
            'reference_number' => 'PO-ENQ-100',
            'date_expected' => now()->addWeek()->toDateString(),
        ], $token);

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND, $enquiry->status);
        $this->assertSame(CommercialEnquirySyncService::SOURCE_WALK_IN, $enquiry->source_channel);
        $this->assertSame((string) $quotation->id, (string) $enquiry->created_from_quotation_header_id);
        $this->assertSame('PO-ENQ-100', $enquiry->reference_number);
        $this->assertNotSame((string) $quotation->id, (string) $enquiry->current_quotation_header_id);
        $this->assertContains((string) $element->id, $enquiry->parameter_ids);
        $this->assertCount(2, $enquiry->enquiry_sample_configuration);

        $ownedQuotation = $enquiry->currentQuotation;
        $this->assertNotNull($ownedQuotation);
        $this->assertSame((string) $quotation->id, (string) $ownedQuotation->source_quotation_header_id);
        $this->assertSame((string) $enquiry->id, (string) $ownedQuotation->sample_submission_request_id);
        $this->assertNotSame((string) $quotation->quote_number, (string) $ownedQuotation->quote_number);
        $this->assertTrue(
            \App\Services\Commercial\AmSpecQuotationNumberGenerator::isAmsqFormat($ownedQuotation->quote_number),
            'Owned clone should receive a normal AMSQ quote number, not a -E suffix.',
        );
        $this->assertCount(1, $ownedQuotation->details);

        $quotation->refresh();
        $this->assertNull($quotation->sample_submission_request_id);

        $retried = app(EnquiryFromQuotationService::class)->create($quotation, [], $token);
        $this->assertSame((string) $enquiry->id, (string) $retried->id);
        $this->assertSame(1, SampleSubmissionRequest::query()
            ->where('quotation_creation_token', $token)
            ->count());
    }

    public function test_it_rejects_general_quotations(): void
    {
        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'quotation_type' => 'General',
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'item_name' => 'Consulting',
            'quantity' => 1,
            'unit_price' => 100,
            'tax' => 0,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Only analysis quotations');

        app(EnquiryFromQuotationService::class)->create(
            $quotation,
            ['number_of_samples' => 1],
            (string) Str::uuid(),
        );
    }

    public function test_it_rejects_analysis_type_only_lines_without_selected_parameters(): void
    {
        $sampleType = SampleType::query()->create([
            'name' => 'Water '.Str::random(6),
            'code' => 'W-'.Str::upper(Str::random(6)),
            'active' => 1,
        ]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Chemistry '.Str::random(6),
            'code' => 'CHEM-'.Str::upper(Str::random(6)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);
        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'is_draft' => 0,
            'is_complete' => 1,
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'sample_type' => $sampleType->id,
            'part_no' => $analysisType->id,
            'quantity' => 1,
            'unit_price' => 100,
            'tax' => 0,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('explicitly selected parameters');

        app(EnquiryFromQuotationService::class)->create(
            $quotation,
            ['number_of_samples' => 1],
            (string) Str::uuid(),
        );
    }

    public function test_already_sent_intent_marks_quotation_sent(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'creation_intent' => EnquiryFromQuotationService::INTENT_ALREADY_SENT,
        ], (string) Str::uuid());

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_SENT, $enquiry->status);
        $this->assertNotNull($enquiry->quotation_first_sent_to_customer_at);
        $this->assertNotNull($enquiry->currentQuotation?->sent_to_customer_at);
    }

    public function test_accepted_intent_marks_ready_for_reception(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'creation_intent' => EnquiryFromQuotationService::INTENT_ACCEPTED,
            'client_po_number' => 'PO-7788',
            'po_skipped' => false,
        ], (string) Str::uuid());

        $this->assertSame(SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION, $enquiry->status);
        $this->assertSame('PO-7788', $enquiry->client_po_number);
        $this->assertFalse((bool) $enquiry->po_skipped);
        $this->assertNotNull($enquiry->accepted_quotation_header_id);
        $this->assertSame((string) $enquiry->current_quotation_header_id, (string) $enquiry->accepted_quotation_header_id);
    }

    public function test_it_uses_portal_source_channel_when_requested(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'source_channel' => CommercialEnquirySyncService::SOURCE_PORTAL,
        ], (string) Str::uuid());

        $this->assertSame(CommercialEnquirySyncService::SOURCE_PORTAL, $enquiry->source_channel);
        $this->assertNotNull($enquiry->submission_form_instance_id);
    }

    public function test_it_normalizes_quotation_source_channel_to_walk_in(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'source_channel' => 'quotation',
        ], (string) Str::uuid());

        $this->assertSame(CommercialEnquirySyncService::SOURCE_WALK_IN, $enquiry->source_channel);
    }

    public function test_create_and_send_is_idempotent_when_already_sent(): void
    {
        [$quotation] = $this->createMappableQuotation();
        $token = (string) Str::uuid();

        $first = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'creation_intent' => EnquiryFromQuotationService::INTENT_ALREADY_SENT,
        ], $token);

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_SENT, $first->status);

        $second = app(EnquiryFromQuotationService::class)->createAndSend(
            $quotation,
            ['number_of_samples' => 1],
            $token,
            sendPortal: false,
            sendEmail: false,
        );

        $this->assertSame((string) $first->id, (string) $second->id);
        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_SENT, $second->status);
    }

    public function test_inline_lines_from_quotation_lock_vat_from_quotation(): void
    {
        [$quotation] = $this->createMappableQuotation();
        $quotation->details()->update(['tax' => 16]);

        $lines = app(\App\Services\Commercial\QuotationFromEnquiryService::class)
            ->buildInlineLinesFromQuotationHeader($quotation->fresh(['details']));

        $this->assertNotEmpty($lines);
        $this->assertTrue((bool) ($lines[0]['vat_from_quotation'] ?? false));
        $this->assertSame(16.0, (float) ($lines[0]['tax'] ?? 0));
    }

    public function test_it_creates_configs_and_allows_multiple_sample_types(): void
    {
        $suffix = Str::upper(Str::random(6));
        [$quotation, $foodTypeId, $waterTypeId] = $this->createMultiTypeQuotation($suffix);

        $service = app(EnquiryFromQuotationService::class);
        $groups = $service->fillableTrfGroups($quotation);
        $this->assertCount(2, $groups);

        $enquiry = $service->create($quotation, [
            'number_of_samples' => 3,
            'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
        ], (string) Str::uuid());

        $configs = $enquiry->enquiry_sample_configuration;
        $this->assertGreaterThanOrEqual(2, count($configs));

        $configTypeIds = collect($configs)
            ->map(static fn (array $config): string => (string) ($config['sample_type_id'] ?? ''))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $this->assertEqualsCanonicalizing([$foodTypeId, $waterTypeId], $configTypeIds);
        $this->assertSame(CommercialEnquirySyncService::SOURCE_WALK_IN, $enquiry->source_channel);
        $this->assertNotNull($enquiry->submission_form_instance_id);

        $linkedTrfCount = \App\Models\SubmissionFormInstance::query()
            ->where('portal_request_id', (string) $enquiry->id)
            ->count();
        $this->assertGreaterThanOrEqual(1, $linkedTrfCount);
    }

    /**
     * @return array{QuotationHeader, AnalysisElements}
     */
    private function createMappableQuotation(): array
    {
        $suffix = Str::upper(Str::random(6));
        $sampleType = SampleType::query()->create([
            'name' => 'Food '.$suffix,
            'code' => 'FOOD-'.$suffix,
            'active' => 1,
        ]);
        $analysisType = AnalysisType::query()->create([
            'name' => 'Microbiology '.$suffix,
            'code' => 'MIC-'.$suffix,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);
        $analyte = Analyte::query()->create([
            'name' => 'Salmonella '.$suffix,
            'code' => 'SALM-'.$suffix,
            'active' => 1,
        ]);
        $element = AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
            'level' => 1,
        ]);
        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'quote_number' => 'Q-'.$suffix,
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'sub_total' => 200,
            'total_amount' => 200,
            'is_draft' => 0,
            'is_complete' => 1,
            'is_approved' => 1,
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'sample_type' => $sampleType->id,
            'part_no' => $analysisType->id,
            'default_analytes' => $element->id,
            'accredited_analytes' => $element->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax' => 0,
            'description' => 'Salmonella',
        ]);

        return [$quotation->fresh(['details']), $element];
    }

    /**
     * @return array{QuotationHeader, string, string}
     */
    private function createMultiTypeQuotation(string $suffix): array
    {
        $foodType = SampleType::query()->create([
            'name' => 'Food '.$suffix,
            'code' => 'FOOD-'.$suffix,
            'active' => 1,
        ]);
        $waterType = SampleType::query()->create([
            'name' => 'Water '.$suffix,
            'code' => 'WATER-'.$suffix,
            'active' => 1,
        ]);

        $foodAnalysis = AnalysisType::query()->create([
            'name' => 'Micro '.$suffix,
            'code' => 'MIC-'.$suffix,
            'sample_type_id' => $foodType->id,
            'active' => 1,
        ]);
        $waterAnalysis = AnalysisType::query()->create([
            'name' => 'Chem '.$suffix,
            'code' => 'CHEM-'.$suffix,
            'sample_type_id' => $waterType->id,
            'active' => 1,
        ]);

        $foodAnalyte = Analyte::query()->create([
            'name' => 'Salmonella '.$suffix,
            'code' => 'SALM-'.$suffix,
            'active' => 1,
        ]);
        $waterAnalyte = Analyte::query()->create([
            'name' => 'pH '.$suffix,
            'code' => 'PH-'.$suffix,
            'active' => 1,
        ]);

        $foodElement = AnalysisElements::query()->create([
            'analysis_type_id' => $foodAnalysis->id,
            'analyte_id' => $foodAnalyte->id,
            'active' => 1,
            'level' => 1,
        ]);
        $waterElement = AnalysisElements::query()->create([
            'analysis_type_id' => $waterAnalysis->id,
            'analyte_id' => $waterAnalyte->id,
            'active' => 1,
            'level' => 1,
        ]);

        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'quote_number' => 'Q-MT-'.$suffix,
            'quotation_type' => 'Analysis',
            'status' => 'Quote Complete',
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addDays(10)->toDateString(),
            'sub_total' => 500,
            'total_amount' => 500,
            'is_draft' => 0,
            'is_complete' => 1,
            'is_approved' => 1,
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'sample_type' => $foodType->id,
            'part_no' => $foodAnalysis->id,
            'default_analytes' => $foodElement->id,
            'accredited_analytes' => $foodElement->id,
            'quantity' => 2,
            'unit_price' => 100,
            'tax' => 0,
            'description' => 'Salmonella',
        ]);
        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'sample_type' => $waterType->id,
            'part_no' => $waterAnalysis->id,
            'default_analytes' => $waterElement->id,
            'accredited_analytes' => $waterElement->id,
            'quantity' => 1,
            'unit_price' => 300,
            'tax' => 0,
            'description' => 'pH',
        ]);

        return [
            $quotation->fresh(['details']),
            (string) $foodType->id,
            (string) $waterType->id,
        ];
    }
}
