<?php

namespace Tests\Unit\Services\Commercial;

use App\AnalysisElements;
use App\AnalysisType;
use App\Analyte;
use App\Models\EnquiryQuotation;
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

    public function test_it_creates_an_enquiry_linked_to_the_billing_quotation_without_cloning(): void
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
        $this->assertSame((string) $quotation->id, (string) $enquiry->current_quotation_header_id);
        $this->assertContains((string) $element->id, $enquiry->parameter_ids);
        $this->assertCount(2, $enquiry->enquiry_sample_configuration);

        $this->assertDatabaseHas('enquiry_quotations', [
            'sample_submission_request_id' => $enquiry->id,
            'quotation_header_id' => $quotation->id,
            'link_source' => EnquiryQuotation::LINK_SOURCE_BILLING_WIZARD,
        ]);

        $quotation->refresh();
        $this->assertNull($quotation->sample_submission_request_id);
        $this->assertSame(1, QuotationHeader::query()->where('id', $quotation->id)->count());

        $retried = app(EnquiryFromQuotationService::class)->create($quotation, [], $token);
        $this->assertSame((string) $enquiry->id, (string) $retried->id);
        $this->assertSame(1, SampleSubmissionRequest::query()
            ->where('quotation_creation_token', $token)
            ->count());
    }

    public function test_multiple_enquiries_can_link_to_the_same_billing_quotation(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $first = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
        ], (string) Str::uuid());

        $second = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $quotation->crm_customer_id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'walk_in',
        ]);

        app(\App\Services\Commercial\QuotationFromEnquiryService::class)
            ->attachExistingQuotation($second, $quotation);

        $this->assertSame((string) $quotation->id, (string) $first->fresh()->current_quotation_header_id);
        $this->assertSame((string) $quotation->id, (string) $second->fresh()->current_quotation_header_id);
        $this->assertSame(2, EnquiryQuotation::query()->where('quotation_header_id', $quotation->id)->count());
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

    public function test_already_sent_intent_marks_quotation_sent_on_pivot(): void
    {
        [$quotation] = $this->createMappableQuotation();

        $enquiry = app(EnquiryFromQuotationService::class)->create($quotation, [
            'number_of_samples' => 1,
            'creation_intent' => EnquiryFromQuotationService::INTENT_ALREADY_SENT,
        ], (string) Str::uuid());

        $this->assertSame(SampleSubmissionRequest::STATUS_QUOTATION_SENT, $enquiry->status);
        $this->assertNotNull($enquiry->quotation_first_sent_to_customer_at);
        $this->assertDatabaseHas('enquiry_quotations', [
            'sample_submission_request_id' => $enquiry->id,
            'quotation_header_id' => $quotation->id,
        ]);
        $this->assertNotNull(
            EnquiryQuotation::query()
                ->where('sample_submission_request_id', $enquiry->id)
                ->where('quotation_header_id', $quotation->id)
                ->value('sent_to_customer_at')
        );
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

        $waterGroup = collect($groups)->first(
            static fn (array $group): bool => (string) ($group['sample_type_id'] ?? '') === $waterTypeId,
        );
        $this->assertNotNull($waterGroup);
        $wizardFields = collect($waterGroup['sections'] ?? [])->flatMap(
            static fn (array $section): array => $section['fields'] ?? [],
        );
        $qtyField = $wizardFields->firstWhere('name', 'sample_quantity');
        $unitField = $wizardFields->firstWhere('name', 'sample_quantity_unit');
        $this->assertNotNull($qtyField);
        $this->assertNotNull($unitField);
        $descField = $wizardFields->firstWhere('name', 'sample_description');
        $this->assertNotNull($descField);
        $this->assertSame('rich_text', $descField['element_type'] ?? null);
        $this->assertSame('number', $qtyField['element_type'] ?? null);
        $this->assertSame('select', $unitField['element_type'] ?? null);
        $this->assertTrue((bool) ($unitField['render_paired'] ?? false));
        $this->assertNotEmpty($qtyField['unit_options'] ?? []);
        $this->assertNull($wizardFields->firstWhere('name', 'number_of_samples'));

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

    public function test_it_persists_wizard_supplemental_fields_through_configs_sample_lines_and_trf(): void
    {
        [$quotation, $element] = $this->createMappableQuotation();
        unset($element);

        $sampleTypeId = (string) $quotation->details->first()->sample_type;
        $service = app(EnquiryFromQuotationService::class);

        $enquiry = $service->create($quotation, [
            'number_of_samples' => 2,
            'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
            'section_field_values_by_type' => [
                $sampleTypeId => [
                    'sample_quantity' => '4',
                    'sample_quantity_unit' => 'L',
                    'sampling_point' => 'Tap',
                    'test_requirements' => 'microbiology',
                ],
            ],
        ], (string) Str::uuid());

        $stored = is_array($enquiry->trf_section_field_values) ? $enquiry->trf_section_field_values : [];
        $this->assertArrayHasKey($sampleTypeId, $stored);
        $this->assertSame('4', $stored[$sampleTypeId]['sample_quantity'] ?? null);

        $configs = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        $this->assertCount(2, $configs);
        foreach ($configs as $config) {
            $this->assertSame('4', $config['sample_quantity'] ?? null);
            $this->assertSame('L', $config['sample_quantity_unit'] ?? null);
            $this->assertSame('Tap', $config['sampling_point'] ?? null);
            $this->assertSame('microbiology', $config['test_requirements'] ?? null);
        }

        $sampleLines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        $this->assertCount(2, $sampleLines);
        foreach ($sampleLines as $line) {
            $this->assertSame('4', $line['sample_quantity'] ?? null);
            $this->assertSame('L', $line['sample_quantity_unit'] ?? null);
            $this->assertSame('Tap', $line['sampling_point'] ?? null);
            $this->assertSame('microbiology', $line['test_requirements'] ?? null);
        }

        $instance = \App\Models\SubmissionFormInstance::query()->find($enquiry->submission_form_instance_id);
        $this->assertNotNull($instance);

        $parsedLines = app(\App\Services\SubmissionForm\SubmissionRequestSampleLineService::class)
            ->linesForInstance($instance->fresh(['values.element', 'submissionForm.sections.elementHolders.elements']));
        $this->assertCount(2, $parsedLines);
        $this->assertSame('4', $parsedLines[0]['sample_quantity'] ?? null);
        $this->assertSame('L', $parsedLines[0]['sample_quantity_unit'] ?? null);
        $this->assertSame('Tap', $parsedLines[0]['sampling_point'] ?? null);
    }

    public function test_it_applies_distinct_per_sample_wizard_supplemental_fields(): void
    {
        [$quotation] = $this->createMappableQuotation();
        $sampleTypeId = (string) $quotation->details->first()->sample_type;
        $service = app(EnquiryFromQuotationService::class);

        $enquiry = $service->create($quotation, [
            'number_of_samples' => 3,
            'creation_intent' => EnquiryFromQuotationService::INTENT_PREPARE,
            'section_field_values_by_type' => [
                $sampleTypeId => [
                    [
                        'sample_quantity' => '4',
                        'sample_quantity_unit' => 'L',
                        'sampling_point' => 'Tap A',
                        'test_requirements' => 'microbiology',
                    ],
                    [
                        'sample_quantity' => '10',
                        'sample_quantity_unit' => 'L',
                        'sampling_point' => 'Tap B',
                        'test_requirements' => 'chemistry',
                    ],
                    [
                        'sample_quantity' => '2',
                        'sample_quantity_unit' => 'L',
                        'sampling_point' => 'Tap C',
                        'test_requirements' => 'microbiology',
                    ],
                ],
            ],
        ], (string) Str::uuid());

        $stored = is_array($enquiry->trf_section_field_values) ? $enquiry->trf_section_field_values : [];
        $this->assertTrue(EnquiryFromQuotationService::isRowIndexedTrfSectionValues($stored[$sampleTypeId] ?? []));
        $this->assertSame('Tap B', $stored[$sampleTypeId][1]['sampling_point'] ?? null);

        $configs = is_array($enquiry->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];
        $this->assertCount(3, $configs);
        $this->assertSame('Tap A', $configs[0]['sampling_point'] ?? null);
        $this->assertSame('Tap B', $configs[1]['sampling_point'] ?? null);
        $this->assertSame('Tap C', $configs[2]['sampling_point'] ?? null);
        $this->assertSame('4', $configs[0]['sample_quantity'] ?? null);
        $this->assertSame('10', $configs[1]['sample_quantity'] ?? null);
        $this->assertSame('2', $configs[2]['sample_quantity'] ?? null);

        $sampleLines = is_array($enquiry->sample_lines) ? $enquiry->sample_lines : [];
        $this->assertCount(3, $sampleLines);
        $this->assertSame('Tap A', $sampleLines[0]['sampling_point'] ?? null);
        $this->assertSame('Tap B', $sampleLines[1]['sampling_point'] ?? null);
        $this->assertSame('Tap C', $sampleLines[2]['sampling_point'] ?? null);
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
