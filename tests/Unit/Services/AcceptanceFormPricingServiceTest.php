<?php

namespace Tests\Unit\Services;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptanceFormPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_line_price_uses_pricelist_item_selling_price(): void
    {
        $pricelist = Pricelist::query()->create([
            'name' => 'Test Pricelist',
            'active' => true,
        ]);

        $sampleTypeId = (string) \Illuminate\Support\Str::uuid();
        $analysisTypeId = (string) \Illuminate\Support\Str::uuid();

        PricelistItem::query()->create([
            'pricelist_id' => $pricelist->id,
            'sample_type_id' => $sampleTypeId,
            'analysis_id' => $analysisTypeId,
            'selling_price' => 125.50,
            'active' => true,
        ]);

        $service = app(AcceptanceFormPricingService::class);
        $price = $service->resolveLinePrice($pricelist, $sampleTypeId, $analysisTypeId);

        $this->assertSame(125.50, $price);
    }

    public function test_deduplicate_removes_analysis_type_only_row_when_elements_exist(): void
    {
        $analysisTypeId = (string) \Illuminate\Support\Str::uuid();
        $elementId = (string) \Illuminate\Support\Str::uuid();

        $lines = [
            [
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => null,
                'parameter_label' => 'Full DNA Analysis',
                'line_no' => 1,
            ],
            [
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => $elementId,
                'parameter_label' => 'pH',
                'line_no' => 2,
            ],
        ];

        $service = app(AcceptanceFormPricingService::class);
        $deduped = $service->deduplicateRedundantAnalysisTypeLines($lines);

        $this->assertCount(1, $deduped);
        $this->assertSame('pH', $deduped[0]['parameter_label']);
        $this->assertSame(1, $deduped[0]['line_no']);
    }

    public function test_deduplicate_keeps_analysis_type_only_row_when_no_elements(): void
    {
        $analysisTypeId = (string) \Illuminate\Support\Str::uuid();

        $lines = [
            [
                'analysis_type_id' => $analysisTypeId,
                'analysis_element_id' => null,
                'parameter_label' => 'Full DNA Analysis',
                'line_no' => 1,
            ],
        ];

        $service = app(AcceptanceFormPricingService::class);
        $deduped = $service->deduplicateRedundantAnalysisTypeLines($lines);

        $this->assertCount(1, $deduped);
        $this->assertSame('Full DNA Analysis', $deduped[0]['parameter_label']);
    }

    public function test_build_prefill_uses_per_row_sample_lines_from_instance(): void
    {
        $formId = (string) \Illuminate\Support\Str::uuid7();
        $instanceId = (string) \Illuminate\Support\Str::uuid7();
        $sampleTypeId = (string) \Illuminate\Support\Str::uuid7();
        $analysisTypeId = (string) \Illuminate\Support\Str::uuid7();
        $elementId = (string) \Illuminate\Support\Str::uuid7();

        \App\Models\SubmissionForm::query()->create([
            'id' => $formId,
            'name' => 'Prefill Form',
            'document_code' => 'PF/001',
            'description' => 'Test',
            'naming_convention_prefix' => 'CR',
            'naming_convention_format' => '{prefix}/{year}/{sequence}',
            'is_published' => true,
            'is_active' => true,
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
            'form_type' => 'template',
            'placement_mode' => 'button_trigger',
            'display_mode' => 'expanded',
            'target_pages' => [],
            'lims_destination_pages' => [],
        ]);

        SubmissionFormInstance::query()->create([
            'id' => $instanceId,
            'submission_form_id' => $formId,
            'title' => 'Prefill test',
            'status' => 'submitted',
            'priority' => 'normal',
        ]);

        $this->mock(SubmissionRequestSampleLineService::class, function ($mock) use ($instanceId, $sampleTypeId, $analysisTypeId, $elementId) {
            $mock->shouldReceive('seedsForAcceptancePrefill')
                ->once()
                ->andReturn([
                    [
                        'sample_type_id' => $sampleTypeId,
                        'analysis_type_id' => $analysisTypeId,
                        'analysis_element_id' => $elementId,
                        'parameter_label' => 'Lead',
                        'customer_sample_id' => 'S-1',
                    ],
                    [
                        'sample_type_id' => $sampleTypeId,
                        'analysis_type_id' => $analysisTypeId,
                        'analysis_element_id' => (string) \Illuminate\Support\Str::uuid7(),
                        'parameter_label' => 'Mercury',
                        'customer_sample_id' => 'S-2',
                    ],
                ]);
        });

        $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(null, $instanceId);

        $this->assertCount(2, $prefill['parameters']);
        $this->assertSame(2, $prefill['number_of_samples']);
        $this->assertSame('Lead', $prefill['parameters'][0]['parameter_label']);
        $this->assertSame($analysisTypeId, $prefill['parameters'][0]['analysis_type_id']);
    }
}
