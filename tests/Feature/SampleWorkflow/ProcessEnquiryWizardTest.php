<?php

namespace Tests\Feature\SampleWorkflow;

use App\Livewire\Sampleworkflow\ProcessEnquiryWizard;
use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\SampleSubmissionRequest;
use App\QuotationDetails;
use App\QuotationHeader;
use App\Services\Commercial\CommercialEnquiryConfigSyncService;
use App\Services\Commercial\QuotationFromEnquiryService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessEnquiryWizardTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_sync_does_not_invent_samples_from_contract_pricelist_by_default(): void
    {
        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $requestedElementId = (string) Str::uuid();
        $extraPricelistElementId = (string) Str::uuid();

        $pricelist = Pricelist::query()->create([
            'name' => 'Contract',
            'active' => true,
        ]);

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        foreach ([$requestedElementId, $extraPricelistElementId] as $elementId) {
            PricelistItem::query()->create([
                'pricelist_id' => $pricelist->id,
                'sample_type_id' => $sampleTypeId,
                'analysis_id' => $analysisTypeId,
                'analysis_element_id' => $elementId,
                'selling_price' => 80,
                'vat' => true,
                'active' => true,
            ]);
        }

        $existingConfigs = [
            [
                'id' => (string) Str::uuid(),
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'parameter_keys' => [$requestedElementId],
                'number_of_samples' => 1,
                'row_index' => 0,
            ],
        ];

        $trfSeeds = [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $requestedElementId,
                'parameter_label' => 'pH',
                'number_of_samples' => 1,
                'row_index' => 0,
            ],
        ];

        $merged = app(CommercialEnquiryConfigSyncService::class)->mergePricelistIntoSampleConfigs(
            $customerId,
            $existingConfigs,
            $trfSeeds,
            false,
            $pricelist,
        );

        $this->assertCount(1, $merged);
        $this->assertSame([$requestedElementId], $merged[0]['parameter_keys']);
        $this->assertNotContains($extraPricelistElementId, $merged[0]['parameter_keys']);
    }

    public function test_build_inline_lines_from_acceptance_lines_sets_physical_sample_count(): void
    {
        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $lines = app(QuotationFromEnquiryService::class)->buildInlineLinesFromAcceptanceLines($enquiry, [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Carbohydrates',
                'number_of_samples' => 2,
                'acceptance_config_key' => 'cfg-1',
            ],
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(2, $lines[0]['physical_sample_count']);
        $this->assertSame(2, $lines[0]['quantity']);
    }

    public function test_build_inline_lines_from_acceptance_lines_applies_vat_from_pricelist(): void
    {
        \App\TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 10,
            'active' => true,
        ]);

        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        $pricelist = Pricelist::query()->create([
            'name' => 'PL',
            'active' => true,
        ]);

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'selling_price' => 120,
            'vat' => true,
            'active' => true,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
            'pricing_source' => 'customer_pricelist',
        ]);

        $acceptanceLines = [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => null,
                'parameter_label' => 'General test',
                'number_of_samples' => 2,
            ],
        ];

        $lines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);

        $this->assertCount(1, $lines);
        $this->assertSame(10.0, (float) $lines[0]['tax']);
        $this->assertTrue((bool) ($lines[0]['vat_from_pricelist'] ?? false));
        $this->assertArrayHasKey('loq', $lines[0]);
        $this->assertArrayHasKey('mu_percent', $lines[0]);
    }

    public function test_build_inline_lines_from_acceptance_lines_uses_zero_tax_without_assigned_pricelist(): void
    {
        \App\TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 18,
            'active' => true,
        ]);

        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $acceptanceLines = [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => null,
                'parameter_label' => 'General test',
                'number_of_samples' => 1,
            ],
        ];

        $lines = app(QuotationFromEnquiryService::class)
            ->buildInlineLinesFromAcceptanceLines($enquiry, $acceptanceLines);

        $this->assertCount(1, $lines);
        $this->assertSame(0.0, (float) $lines[0]['tax']);
    }

    public function test_build_inline_lines_uses_pricelist_price_when_unit_amount_is_zero(): void
    {
        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $pricelist = Pricelist::query()->create([
            'name' => 'Master',
            'active' => true,
            'is_master' => true,
        ]);

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'analysis_element_id' => $elementId,
            'selling_price' => 110,
            'vat' => true,
            'active' => true,
            'is_package' => false,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $lines = app(QuotationFromEnquiryService::class)->buildInlineLinesFromAcceptanceLines($enquiry, [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Aflatoxin B1',
                'unit_amount' => 0,
                'number_of_samples' => 1,
            ],
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(110.0, (float) $lines[0]['unit_price']);
    }

    public function test_build_inline_lines_keeps_positive_provided_unit_amount(): void
    {
        $customerId = (string) Str::uuid();
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $elementId = (string) Str::uuid();

        $pricelist = Pricelist::query()->create([
            'name' => 'Master',
            'active' => true,
            'is_master' => true,
        ]);

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'analysis_element_id' => $elementId,
            'selling_price' => 110,
            'vat' => true,
            'active' => true,
            'is_package' => false,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $lines = app(QuotationFromEnquiryService::class)->buildInlineLinesFromAcceptanceLines($enquiry, [
            [
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Aflatoxin B1',
                'unit_amount' => 95,
                'number_of_samples' => 1,
            ],
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(95.0, (float) $lines[0]['unit_price']);
    }

    public function test_build_inline_lines_resolves_price_when_trf_element_differs_from_pricelist_element(): void
    {
        \App\TaxRegime::query()->create([
            'id' => (string) Str::uuid(),
            'value' => 5,
            'active' => true,
        ]);

        $customerId = (string) Str::uuid();
        $suffix = Str::upper(Str::random(4));

        $sampleType = \App\SampleType::query()->create([
            'name' => 'Food Sync '.$suffix,
            'code' => 'FOOD-SYNC-'.$suffix,
            'active' => 1,
        ]);
        $analysisType = \App\AnalysisType::query()->create([
            'name' => 'Food and feed Sync '.$suffix,
            'code' => 'FF-SYNC-'.$suffix,
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);
        $analyte = \App\Analyte::query()->create([
            'name' => 'Bacillus cereus Sync '.$suffix,
            'code' => 'BACILLUS_SYNC_'.$suffix,
            'active' => 1,
        ]);

        $pricedElement = \App\AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
            'level' => 1,
        ]);
        $trfElement = \App\AnalysisElements::query()->create([
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
            'level' => 2,
        ]);

        $pricelist = Pricelist::query()->create([
            'code' => 'PL-SYNC-'.$suffix,
            'description' => 'Master sync fallback',
            'active' => true,
            'is_master' => true,
        ]);

        PricelistCustomer::query()->create([
            'id' => (string) Str::uuid(),
            'pricelist_id' => $pricelist->id,
            'customer_id' => $customerId,
        ]);

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleType->id,
            'analysis_id' => $analysisType->id,
            'analysis_element_id' => $pricedElement->id,
            'selling_price' => 93,
            'vat' => true,
            'active' => true,
            'is_package' => false,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $lines = app(QuotationFromEnquiryService::class)->buildInlineLinesFromAcceptanceLines($enquiry, [
            [
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'analysis_element_id' => (string) $trfElement->id,
                'parameter_label' => (string) $analyte->name,
                'unit_amount' => 0,
                'number_of_samples' => 1,
            ],
        ]);

        $this->assertCount(1, $lines);
        $this->assertSame(93.0, (float) $lines[0]['unit_price']);
        $this->assertSame(5.0, (float) $lines[0]['tax']);
        $this->assertTrue((bool) ($lines[0]['vat_from_pricelist'] ?? false));
    }

    public function test_switching_back_to_build_new_clears_existing_quotation_approval_state(): void
    {
        Livewire::test(ProcessEnquiryWizard::class)
            ->set('quotationMode', 'use_existing')
            ->set('selectedExistingQuotationId', (string) Str::uuid())
            ->set('quotationHeaderId', (string) Str::uuid())
            ->set('quoteNumber', 'AMSQ260802-001')
            ->set('quotationApprovedReadyToSend', true)
            ->set('quotationReviewedByName', 'Lab Manager')
            ->call('setQuotationMode', 'build_new')
            ->assertSet('selectedExistingQuotationId', null)
            ->assertSet('quotationHeaderId', null)
            ->assertSet('quoteNumber', '')
            ->assertSet('quotationApprovedReadyToSend', false)
            ->assertSet('quotationReviewedByName', '')
            ->assertSet('requiresNewQuotationHeader', true);
    }

    public function test_create_new_from_enquiry_does_not_reuse_an_approved_current_quotation(): void
    {
        $user = User::query()->create([
            'name' => 'Quotation Builder',
            'email' => 'quotation.builder@example.test',
            'password' => bcrypt('password'),
        ]);
        $this->actingAs($user);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS,
            'source_channel' => 'walk_in',
        ]);

        $service = app(QuotationFromEnquiryService::class);
        $approvedHeader = $service->createNewFromEnquiry($enquiry);
        $approvedHeader->forceFill([
            'status' => 'Quote Complete',
            'is_complete' => 1,
            'is_approved' => 1,
        ])->save();

        $enquiry = $service->detachExistingQuotationFromEnquiry(
            $enquiry->fresh(),
            (string) $approvedHeader->id,
        );
        $this->assertNull($enquiry->current_quotation_header_id);

        $newHeader = $service->createOrOpen($enquiry);

        $this->assertNotSame((string) $approvedHeader->id, (string) $newHeader->id);
        $this->assertSame(0, (int) $newHeader->is_approved);
        $this->assertSame(0, (int) $newHeader->is_complete);
        $this->assertSame('Quote In Preparation', (string) $newHeader->status);
    }

    public function test_selecting_a_previously_sent_existing_quotation_does_not_mark_new_enquiry_as_sent(): void
    {
        $customerId = (string) Str::uuid();
        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'walk_in',
            'quotation_first_sent_to_customer_at' => null,
        ]);
        $existingHeader = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'quote_date' => now()->subDay()->toDateString(),
            'expiring_date' => now()->addMonth()->toDateString(),
            'status' => 'Quote Complete',
            'is_complete' => 1,
            'is_approved' => 1,
            'sent_to_customer_at' => now()->subDay(),
        ]);

        Livewire::test(ProcessEnquiryWizard::class)
            ->set('enquiryId', (string) $enquiry->id)
            ->set('crmCustomerId', $customerId)
            ->call('setQuotationMode', 'use_existing')
            ->call('selectExistingQuotation', (string) $existingHeader->id)
            ->assertSet('quotationSent', false)
            ->assertSet('quotationApprovedReadyToSend', true);
    }

    public function test_open_wizard_for_enquiry_created_from_quotation_uses_existing_mode(): void
    {
        $customerId = (string) Str::uuid();
        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addMonth()->toDateString(),
            'status' => 'Quote Complete',
            'is_complete' => 1,
            'is_approved' => 1,
            'upload_url' => 'quotations/test-quote.pdf',
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'item_name' => 'Salmonella',
            'quantity' => 2,
            'unit_price' => 100,
            'tax' => 0,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
            'source_channel' => 'walk_in',
            'created_from_quotation_header_id' => $quotation->id,
            'current_quotation_header_id' => $quotation->id,
            'pricing_source' => 'existing_quotation',
        ]);

        Livewire::test(ProcessEnquiryWizard::class)
            ->call('openWizard', (string) $enquiry->id)
            ->assertSet('quotationMode', 'use_existing')
            ->assertSet('selectedExistingQuotationId', (string) $quotation->id)
            ->assertSet('pdfGenerated', true)
            ->assertSet('quotationBuilt', true);
    }

    public function test_view_quotation_for_enquiry_from_quotation_opens_existing_pdf_without_regeneration(): void
    {
        $customerId = (string) Str::uuid();
        $quotation = QuotationHeader::query()->create([
            'crm_customer_id' => $customerId,
            'quote_date' => now()->toDateString(),
            'expiring_date' => now()->addMonth()->toDateString(),
            'status' => 'Quote Complete',
            'is_complete' => 1,
            'is_approved' => 1,
            'upload_url' => 'quotations/test-quote.pdf',
        ]);

        QuotationDetails::query()->create([
            'quotation_header_id' => $quotation->id,
            'item_name' => 'Salmonella',
            'quantity' => 2,
            'unit_price' => 100,
            'tax' => 0,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $customerId,
            'status' => SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
            'source_channel' => 'walk_in',
            'created_from_quotation_header_id' => $quotation->id,
            'current_quotation_header_id' => $quotation->id,
            'pricing_source' => 'existing_quotation',
        ]);

        Livewire::test(ProcessEnquiryWizard::class)
            ->call('openWizard', (string) $enquiry->id)
            ->call('viewQuotation')
            ->assertDispatched('open-quotation-preview');
    }
}
