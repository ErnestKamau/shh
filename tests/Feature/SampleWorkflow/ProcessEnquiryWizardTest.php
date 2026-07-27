<?php

namespace Tests\Feature\SampleWorkflow;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistCustomer;
use App\Models\Billing\PricelistItem;
use App\Models\SampleSubmissionRequest;
use App\Services\Commercial\CommercialEnquiryConfigSyncService;
use App\Services\Commercial\QuotationFromEnquiryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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
}
