<?php

namespace Tests\Unit\Services;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisType;
use App\Models\SampleSubmissionRequest;
use App\Models\SampleSubmissionRequestRequestedAnalysis;
use App\Models\SubmissionFormInstance;
use App\SampleType;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class AcceptanceFormSampleConfigServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_build_configs_creates_one_config_per_physical_sample_row(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'customer_sample_id' => 'CUST-1',
            ],
            [
                'row_index' => 1,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'customer_sample_id' => 'CUST-2',
            ],
            [
                'row_index' => 2,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'customer_sample_id' => 'CUST-3',
            ],
        ]);

        $this->assertCount(3, $configs);
        $this->assertSame(1, $configs[0]['number_of_samples']);
        $this->assertSame('CUST-1', $configs[0]['customer_sample_id']);
        $this->assertSame('CUST-2', $configs[1]['customer_sample_id']);
        $this->assertSame('CUST-3', $configs[2]['customer_sample_id']);
    }

    public function test_build_prefill_from_enquiry_uses_trf_sample_lines_not_quotation_parameters(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $enquiry = new SampleSubmissionRequest([
            'sample_type_id' => 'st-food',
            'matrix_id' => 'at-cooked',
            'number_of_samples' => 1,
            'sample_lines' => [
                [
                    'sort_order' => 0,
                    'sample_type_id' => 'st-food',
                    'analysis_type_id' => 'at-cooked',
                    'number_of_samples' => 1,
                    'attributes' => [
                        'analysis_element_ids' => ['el-carb', 'el-energy'],
                    ],
                ],
            ],
        ]);

        $quotationLines = [
            [
                'sample_type_id' => 'st-food',
                'analysis_type_id' => 'at-cooked',
                'analysis_element_id' => 'el-carb',
                'parameter_label' => 'Carbohydrates',
                'quantity' => 1,
            ],
            [
                'sample_type_id' => 'st-food',
                'analysis_type_id' => 'at-cooked',
                'analysis_element_id' => 'el-energy',
                'parameter_label' => 'Energy',
                'quantity' => 1,
            ],
        ];

        $prefill = $service->buildPrefillLinesFromEnquiry($enquiry, $quotationLines);
        $configs = $service->buildConfigsFromPrefill($prefill);

        $this->assertCount(1, $prefill);
        $this->assertCount(1, $configs);
        $this->assertSame(['el-carb', 'el-energy'], $configs[0]['parameter_keys']);
    }

    public function test_build_prefill_from_enquiry_merges_multiple_quotation_lines_for_single_sample(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $enquiry = new SampleSubmissionRequest([
            'number_of_samples' => 1,
            'sample_lines' => [],
        ]);

        $quotationLines = [
            [
                'sample_type_id' => 'st-food',
                'analysis_type_id' => 'at-cooked',
                'analysis_element_id' => 'el-carb',
                'parameter_label' => 'Carbohydrates',
            ],
            [
                'sample_type_id' => 'st-food',
                'analysis_type_id' => 'at-cooked',
                'analysis_element_id' => 'el-energy',
                'parameter_label' => 'Energy',
            ],
        ];

        $prefill = $service->buildPrefillLinesFromEnquiry($enquiry, $quotationLines);
        $configs = $service->buildConfigsFromPrefill($prefill);

        $this->assertCount(1, $prefill);
        $this->assertCount(1, $configs);
        $this->assertSame(['el-carb', 'el-energy'], $configs[0]['parameter_keys']);
    }

    public function test_build_configs_merges_parameters_on_same_row_index(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
            ],
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-2',
            ],
            [
                'row_index' => 1,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-2',
                'analysis_element_id' => 'el-3',
            ],
        ]);

        $this->assertCount(2, $configs);
        $this->assertSame(['el-1', 'el-2'], $configs[0]['parameter_keys']);
        $this->assertSame('at-2', $configs[1]['analysis_type_id']);
    }

    public function test_build_configs_explodes_trf_quantity_into_separate_configs(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'number_of_samples' => 5,
            ],
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-2',
                'number_of_samples' => 5,
            ],
        ]);

        $this->assertCount(5, $configs);
        foreach ($configs as $config) {
            $this->assertSame(1, $config['number_of_samples']);
            $this->assertSame(['el-1', 'el-2'], $config['parameter_keys']);
        }
    }

    public function test_build_configs_expands_analysis_element_ids_from_attributes(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'number_of_samples' => 2,
                'attributes' => [
                    'analysis_element_ids' => ['el-1', 'el-2'],
                ],
            ],
        ]);

        $this->assertCount(2, $configs);
        $this->assertSame(['el-1', 'el-2'], $configs[0]['parameter_keys']);
        $this->assertSame(1, $configs[0]['number_of_samples']);
    }

    public function test_flatten_to_per_sample_configs_splits_legacy_grouped_config(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1'];
        $config['number_of_samples'] = 3;
        $config['instances'] = [
            ['customer_sample_id' => 'A-1', 'sample_marking' => 'M1', 'disposal_date' => '', 'photo_path' => ''],
            ['customer_sample_id' => 'A-2', 'sample_marking' => 'M2', 'disposal_date' => '', 'photo_path' => ''],
            ['customer_sample_id' => 'A-3', 'sample_marking' => 'M3', 'disposal_date' => '', 'photo_path' => ''],
        ];

        $flat = $service->flattenToPerSampleConfigs([$config]);

        $this->assertCount(3, $flat);
        $this->assertSame('A-1', $flat[0]['customer_sample_id']);
        $this->assertSame('A-3', $flat[2]['customer_sample_id']);
        $this->assertSame(1, $flat[0]['number_of_samples']);
    }

    public function test_sync_instances_preserves_existing_values_when_count_increases(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $instances = $service->syncInstances([
            ['customer_sample_id' => 'CUST-1', 'sample_marking' => 'Mark A', 'disposal_date' => '', 'photo_path' => ''],
        ], 3);

        $this->assertCount(3, $instances);
        $this->assertSame('CUST-1', $instances[0]['customer_sample_id']);
        $this->assertSame('Mark A', $instances[0]['sample_marking']);
        $this->assertSame('', $instances[2]['customer_sample_id']);
    }

    public function test_validate_reception_configs_requires_main_standard_and_assigned_user(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1'];

        $this->expectException(ValidationException::class);

        $service->validateReceptionConfigs([$config]);
    }

    public function test_validate_reception_configs_can_skip_parameter_assignments(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['main_standard_id'] = 'std-1';
        $config['parameter_keys'] = [];

        $service->validateReceptionConfigs([$config], requireParameterAssignments: false);

        $this->assertTrue(true);
    }

    public function test_validate_reception_configs_does_not_require_lab_fields(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1'];
        $config['main_standard_id'] = 'std-1';
        $config['assigned_user_id'] = 'user-1';
        $config['lab_id'] = null;
        $config['lab_section_id'] = null;

        $service->validateReceptionConfigs([$config]);

        $this->assertTrue(true);
    }

    public function test_validate_configs_requires_parameters(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $this->expectException(ValidationException::class);

        $service->validateConfigs([
            $service->emptyConfig(),
        ]);
    }

    public function test_build_detail_plans_from_configs_creates_one_plan_per_sample_config(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1', 'el-2'];
        $config['customer_sample_id'] = 'A-1';
        $config['sample_marking'] = 'M1';
        $config['disposal_date'] = '2026-08-01';
        $config['photo_path'] = 'sample_photos/a.jpg';

        $plans = $service->buildDetailPlansFromConfigs([$config]);

        $this->assertCount(1, $plans);
        $this->assertSame(['at-1'], $plans[0]['analysis_type_ids']);
        $this->assertSame(['el-1', 'el-2'], $plans[0]['analysis_element_ids']);
        $this->assertSame('A-1', $plans[0]['customer_sample_id']);
        $this->assertSame('2026-08-01', $plans[0]['disposal_date']);
        $this->assertSame('sample_photos/a.jpg', $plans[0]['photo_path']);
    }

    public function test_build_detail_plans_flattens_legacy_grouped_config(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1', 'el-2'];
        $config['number_of_samples'] = 2;
        $config['instances'] = [
            ['customer_sample_id' => 'A-1', 'sample_marking' => 'M1', 'disposal_date' => '2026-08-01', 'photo_path' => 'sample_photos/a.jpg'],
            ['customer_sample_id' => 'A-2', 'sample_marking' => 'M2', 'disposal_date' => '', 'photo_path' => ''],
        ];

        $plans = $service->buildDetailPlansFromConfigs([$config]);

        $this->assertCount(2, $plans);
        $this->assertSame('A-1', $plans[0]['customer_sample_id']);
        $this->assertSame('M2', $plans[1]['sample_marking']);
    }

    public function test_total_sample_count_equals_config_count(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $count = $service->totalSampleCount([
            ['number_of_samples' => 1],
            ['number_of_samples' => 1],
            ['number_of_samples' => 1],
        ]);

        $this->assertSame(3, $count);
    }

    public function test_reconcile_parameter_keys_drops_stale_trf_element_ids(): void
    {
        $config = app(AcceptanceFormSampleConfigService::class)->emptyConfig();
        $config['sample_type_id'] = 'st-food';
        $config['analysis_type_id'] = 'at-cooked';
        $config['parameter_keys'] = ['stale-trf-element-id', 'visible-element-id'];

        $pricing = \Mockery::mock(AcceptanceFormPricingService::class);
        $pricing->shouldReceive('parametersForAddLineSelection')
            ->once()
            ->with('cust-1', 'st-food', 'at-cooked')
            ->andReturn([
                [
                    'id' => 'visible-element-id',
                    'analysis_element_id' => 'visible-element-id',
                    'label' => 'Moisture and volatile matter',
                    'unit_amount' => 10.0,
                ],
            ]);

        $service = new AcceptanceFormSampleConfigService(
            $pricing,
            app(\App\Services\Sampleworkflow\InterzoneTransferService::class),
        );

        $reconciled = $service->reconcileParameterKeysForConfig($config, 'cust-1');

        $this->assertSame(['visible-element-id'], $reconciled['parameter_keys']);
    }

    public function test_sync_parameter_keys_from_quotation_lines_overwrites_stale_names(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['Carbohydrates'];

        $elementId = (string) \Illuminate\Support\Str::uuid();

        $synced = $service->syncParameterKeysFromQuotationLines([$config], [
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => $elementId,
                'parameter_label' => 'Carbohydrates',
            ],
        ]);

        $this->assertSame([$elementId], $synced[0]['parameter_keys']);
    }

    public function test_resolve_element_ids_for_analysis_type_rejects_non_uuid_labels_without_match(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $resolved = $service->resolveElementIdsForAnalysisType(['Carbohydrates', 'UnknownParam'], 'missing-at');

        $this->assertSame([], $resolved);
    }

    public function test_resolve_lab_section_id_falls_back_to_analysis_elements(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $existing = \App\AnalysisType::query()->whereNotNull('company_id')->whereNotNull('lab_id')->whereNotNull('sample_type_id')->first();
        $companyId = $existing?->company_id ?: (string) \Illuminate\Support\Str::uuid();
        $labId = $existing?->lab_id ?: (string) \Illuminate\Support\Str::uuid();
        $sampleTypeId = $existing?->sample_type_id ?: (string) \Illuminate\Support\Str::uuid();

        $stageId = (string) \Illuminate\Support\Str::uuid();

        \Illuminate\Support\Facades\DB::table('sample_analysis_stages')->insert([
            'id' => $stageId,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'name' => 'Test Section',
            'code' => 'TST',
            'active' => true,
            'is_sample_stage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $analysisType = \App\AnalysisType::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Type No Lab Section',
            'code' => 'TTNLS',
            'sample_type_id' => $sampleTypeId,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'lab_section_id' => null,
            'active' => 1,
        ]);

        $analyteId = \App\AnalysisElements::query()->whereNotNull('analyte_id')->value('analyte_id') ?: (string) \Illuminate\Support\Str::uuid();

        \App\AnalysisElements::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'lab_section_id' => $stageId,
            'analyte_id' => $analyteId,
            'active' => 1,
        ]);

        $resolved = $service->resolveLabSectionIdForAnalysisType($analysisType->id);

        $this->assertSame($stageId, $resolved);
    }

    public function test_remap_configs_to_current_hierarchy_maps_orphan_ids_by_analyte_label(): void
    {
        $oldSampleTypeId = (string) Str::uuid();
        $oldAnalysisTypeId = (string) Str::uuid();
        $orphanElementId = (string) Str::uuid();
        $sampleTypeName = 'Food Remap '.Str::random(6);
        $analysisTypeName = 'Feed Remap '.Str::random(6);
        $analyteName = 'Barium Remap '.Str::random(6);

        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $sampleTypeName,
            'code' => 'FOOD-REMAP-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $analysisTypeName,
            'code' => 'FF-REMAP-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'BA-'.Str::upper(Str::random(4)),
            'name' => $analyteName,
            'active' => 1,
        ]);

        $currentElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
            'sample_lines' => [[
                'sample_type_id' => $oldSampleTypeId,
                'sample_type_name' => $sampleTypeName,
                'analysis_type_id' => $oldAnalysisTypeId,
                'analysis_type_name' => $analysisTypeName,
                'analysis_element_id' => $orphanElementId,
                'parameter_label' => $analyteName,
                'number_of_samples' => 1,
                'attributes' => [
                    'analysis_element_ids' => [$orphanElementId],
                ],
            ]],
        ]);

        SampleSubmissionRequestRequestedAnalysis::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'sample_type_id' => $oldSampleTypeId,
            'analysis_type_id' => $oldAnalysisTypeId,
            'analysis_element_id' => $orphanElementId,
            'analysis_key' => $orphanElementId,
            'analysis_label' => $analyteName,
            'number_of_samples' => 1,
        ]);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = $oldSampleTypeId;
        $config['analysis_type_id'] = $oldAnalysisTypeId;
        $config['parameter_keys'] = [$orphanElementId];

        $remapped = $service->remapConfigsToCurrentHierarchy([$config], $enquiry->fresh(['requestedAnalyses']));

        $this->assertSame((string) $sampleType->id, $remapped[0]['sample_type_id']);
        $this->assertSame((string) $analysisType->id, $remapped[0]['analysis_type_id']);
        $this->assertSame([(string) $currentElement->id], $remapped[0]['parameter_keys']);
    }

    public function test_build_prefill_from_enquiry_uses_instance_when_sample_lines_empty(): void
    {
        $instance = Mockery::mock(SubmissionFormInstance::class);
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [],
            'sample_type_id' => 'st-1',
            'matrix_id' => 'at-1',
            'number_of_samples' => 1,
        ]);

        $lineService = Mockery::mock(SubmissionRequestSampleLineService::class);
        $lineService->shouldReceive('linesForInstance')
            ->once()
            ->with($instance)
            ->andReturn([[
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'attributes' => ['analysis_element_ids' => ['el-1']],
                'parameter_label' => 'Test parameter',
                'number_of_samples' => 1,
            ]]);

        $this->app->instance(SubmissionRequestSampleLineService::class, $lineService);

        $service = app(AcceptanceFormSampleConfigService::class);
        $prefill = $service->buildPrefillLinesFromEnquiry($enquiry, [], $instance);

        $this->assertCount(1, $prefill);
        $this->assertSame(['el-1'], $prefill[0]['attributes']['analysis_element_ids']);
    }

    public function test_build_prefill_from_enquiry_prefers_instance_over_stale_sample_lines(): void
    {
        $instance = Mockery::mock(SubmissionFormInstance::class);
        $enquiry = new SampleSubmissionRequest([
            'sample_lines' => [[
                'row_index' => 0,
                'sample_type_id' => 'st-stale',
                'analysis_type_id' => 'at-stale',
                'attributes' => ['analysis_element_ids' => ['el-stale']],
                'number_of_samples' => 1,
            ]],
            'sample_type_id' => 'st-stale',
            'matrix_id' => 'at-stale',
            'number_of_samples' => 1,
        ]);

        $lineService = Mockery::mock(SubmissionRequestSampleLineService::class);
        $lineService->shouldReceive('linesForInstance')
            ->once()
            ->with($instance)
            ->andReturn([[
                'row_index' => 0,
                'sample_type_id' => 'st-trf',
                'analysis_type_id' => 'at-trf',
                'attributes' => ['analysis_element_ids' => ['el-trf-a', 'el-trf-b']],
                'parameter_label' => 'TRF parameters',
                'number_of_samples' => 1,
            ]]);

        $this->app->instance(SubmissionRequestSampleLineService::class, $lineService);

        $service = app(AcceptanceFormSampleConfigService::class);
        $prefill = $service->buildPrefillLinesFromEnquiry($enquiry, [], $instance);
        $configs = $service->buildConfigsFromPrefill($prefill);

        $this->assertCount(1, $prefill);
        $this->assertSame('st-trf', $prefill[0]['sample_type_id']);
        $this->assertSame('at-trf', $prefill[0]['analysis_type_id']);
        $this->assertSame(['el-trf-a', 'el-trf-b'], $configs[0]['parameter_keys']);
    }

    public function test_apply_requested_parameter_keys_prefers_trf_instance_lines_over_stale_analyses(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Trf Prefer '.Str::random(6),
            'code' => 'FOOD-TRF-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Cooked Trf Prefer '.Str::random(6),
            'code' => 'CKD-TRF-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $trfAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'TRF-'.Str::upper(Str::random(4)),
            'name' => 'TRF Param '.Str::random(6),
            'active' => 1,
        ]);

        $staleAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'STL-'.Str::upper(Str::random(4)),
            'name' => 'Stale Param '.Str::random(6),
            'active' => 1,
        ]);

        $trfElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $trfAnalyte->id,
            'active' => 1,
        ]);

        $staleElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $staleAnalyte->id,
            'active' => 1,
        ]);

        $instance = Mockery::mock(SubmissionFormInstance::class);
        $enquiry = new SampleSubmissionRequest([
            'sample_type_id' => (string) $sampleType->id,
            'matrix_id' => (string) $analysisType->id,
            'sample_lines' => [[
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'attributes' => ['analysis_element_ids' => [(string) $staleElement->id]],
            ]],
        ]);
        $enquiry->setRelation('submissionFormInstance', $instance);
        $enquiry->setRelation('requestedAnalyses', collect([
            new SampleSubmissionRequestRequestedAnalysis([
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'analysis_element_id' => (string) $staleElement->id,
                'analysis_key' => (string) $staleElement->id,
                'analysis_label' => $staleAnalyte->name,
            ]),
        ]));

        $lineService = Mockery::mock(SubmissionRequestSampleLineService::class);
        $lineService->shouldReceive('linesForInstance')
            ->atLeast()
            ->once()
            ->with($instance)
            ->andReturn([[
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'attributes' => ['analysis_element_ids' => [(string) $trfElement->id]],
            ]]);
        $this->app->instance(SubmissionRequestSampleLineService::class, $lineService);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = (string) $sampleType->id;
        $config['analysis_type_id'] = (string) $analysisType->id;
        $config['parameter_keys'] = [];

        $updated = $service->applyRequestedParameterKeysFromEnquiry([$config], $enquiry);

        $this->assertSame([(string) $trfElement->id], $updated[0]['parameter_keys']);
    }

    public function test_apply_requested_parameter_keys_preserves_non_empty_lab_selections(): void
    {
        $sampleTypeId = (string) Str::uuid();
        $analysisTypeId = (string) Str::uuid();
        $labElementId = (string) Str::uuid();
        $trfElementId = (string) Str::uuid();

        $instance = Mockery::mock(SubmissionFormInstance::class);
        $enquiry = new SampleSubmissionRequest([
            'sample_type_id' => $sampleTypeId,
            'matrix_id' => $analysisTypeId,
        ]);
        $enquiry->setRelation('submissionFormInstance', $instance);
        $enquiry->setRelation('requestedAnalyses', collect());

        $lineService = Mockery::mock(SubmissionRequestSampleLineService::class);
        $lineService->shouldReceive('linesForInstance')
            ->atLeast()
            ->once()
            ->with($instance)
            ->andReturn([[
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeId,
                'attributes' => ['analysis_element_ids' => [$trfElementId]],
            ]]);
        $this->app->instance(SubmissionRequestSampleLineService::class, $lineService);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = $sampleTypeId;
        $config['analysis_type_id'] = $analysisTypeId;
        $config['parameter_keys'] = [$labElementId];

        $updated = $service->applyRequestedParameterKeysFromEnquiry([$config], $enquiry);

        $this->assertSame([$labElementId], $updated[0]['parameter_keys']);
    }

    public function test_apply_requested_parameter_keys_from_enquiry_resolves_label_only_requested_analysis(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Apply '.Str::random(6),
            'code' => 'FOOD-APPLY-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'General Foods Apply '.Str::random(6),
            'code' => 'GF-APPLY-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyteName = 'Enumeration of Enterobacteriaceae '.Str::random(6);
        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'ENT-'.Str::upper(Str::random(4)),
            'name' => $analyteName,
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
            'sample_lines' => [[
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'number_of_samples' => 1,
            ]],
        ]);

        SampleSubmissionRequestRequestedAnalysis::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'sample_type_id' => (string) $sampleType->id,
            'analysis_type_id' => (string) $analysisType->id,
            'analysis_element_id' => null,
            'analysis_key' => $analyteName,
            'analysis_label' => $analyteName,
            'number_of_samples' => 1,
        ]);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = (string) $sampleType->id;
        $config['analysis_type_id'] = (string) $analysisType->id;
        $config['parameter_keys'] = [];

        $updated = $service->applyRequestedParameterKeysFromEnquiry([$config], $enquiry->fresh(['requestedAnalyses']));

        $this->assertSame([(string) $element->id], $updated[0]['parameter_keys']);
    }

    public function test_remap_and_apply_requested_parameter_keys_restores_orphan_element_ids(): void
    {
        $oldSampleTypeId = (string) Str::uuid();
        $oldAnalysisTypeId = (string) Str::uuid();
        $orphanElementId = (string) Str::uuid();
        $sampleTypeName = 'Food Remap Apply '.Str::random(6);
        $analysisTypeName = 'Feed Remap Apply '.Str::random(6);
        $analyteName = 'Barium Remap Apply '.Str::random(6);

        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $sampleTypeName,
            'code' => 'FOOD-RAP-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => $analysisTypeName,
            'code' => 'FF-RAP-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'BA-RAP-'.Str::upper(Str::random(4)),
            'name' => $analyteName,
            'active' => 1,
        ]);

        $currentElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
            'sample_lines' => [[
                'sample_type_id' => $oldSampleTypeId,
                'sample_type_name' => $sampleTypeName,
                'analysis_type_id' => $oldAnalysisTypeId,
                'analysis_type_name' => $analysisTypeName,
                'analysis_element_id' => $orphanElementId,
                'parameter_label' => $analyteName,
                'number_of_samples' => 1,
                'attributes' => [
                    'analysis_element_ids' => [$orphanElementId],
                ],
            ]],
        ]);

        SampleSubmissionRequestRequestedAnalysis::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'sample_type_id' => $oldSampleTypeId,
            'analysis_type_id' => $oldAnalysisTypeId,
            'analysis_element_id' => $orphanElementId,
            'analysis_key' => $orphanElementId,
            'analysis_label' => $analyteName,
            'number_of_samples' => 1,
        ]);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = $oldSampleTypeId;
        $config['analysis_type_id'] = $oldAnalysisTypeId;
        $config['parameter_keys'] = [];

        $enquiry = $enquiry->fresh(['requestedAnalyses']);
        $configs = $service->applyRequestedParameterKeysFromEnquiry([$config], $enquiry);
        $configs = $service->remapConfigsToCurrentHierarchy($configs, $enquiry);

        $this->assertSame((string) $sampleType->id, $configs[0]['sample_type_id']);
        $this->assertSame((string) $analysisType->id, $configs[0]['analysis_type_id']);
        $this->assertSame([(string) $currentElement->id], $configs[0]['parameter_keys']);
    }

    public function test_build_configs_from_prefill_accumulates_multiple_analysis_types_per_sample(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'attributes' => [
                    'analysis_type_ids' => ['at-1', 'at-2'],
                    'analysis_element_ids' => ['el-1', 'el-2'],
                ],
            ],
        ]);

        $this->assertCount(1, $configs);
        $this->assertSame('at-1', $configs[0]['analysis_type_id']);
        $this->assertSame(['at-1', 'at-2'], $configs[0]['analysis_type_ids']);
        $this->assertSame(['el-1', 'el-2'], $configs[0]['parameter_keys']);
    }

    public function test_build_configs_preserves_multiple_sample_types_on_one_physical_sample(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'attributes' => [
                    'sample_type_ids' => ['st-1', 'st-2'],
                    'analysis_type_ids' => ['at-1', 'at-2'],
                ],
            ],
        ]);

        $this->assertCount(1, $configs);
        $this->assertSame('st-1', $configs[0]['sample_type_id']);
        $this->assertSame(['st-1', 'st-2'], $configs[0]['sample_type_ids']);
        $this->assertTrue($configs[0]['allows_multiple_sample_types']);
        $this->assertSame(['at-1', 'at-2'], $configs[0]['analysis_type_ids']);
    }

    public function test_sample_type_fields_keep_primary_id_for_legacy_consumers(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $config = $service->syncSampleTypeIdsOnConfig(
            $service->emptyConfig(),
            ['st-2', 'st-1', 'st-2'],
        );

        $this->assertSame('st-2', $config['sample_type_id']);
        $this->assertSame(['st-2', 'st-1'], $config['sample_type_ids']);
        $this->assertSame(['st-2', 'st-1'], $service->sampleTypeIdsFromConfig($config));
    }

    public function test_build_configs_merges_analysis_types_when_prefill_lines_share_physical_sample(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'customer_sample_id' => 'CUST-1',
            ],
            [
                'row_index' => 0,
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-2',
                'analysis_element_id' => 'el-2',
                'customer_sample_id' => 'CUST-1',
            ],
        ]);

        $this->assertCount(1, $configs);
        $this->assertSame(['at-1', 'at-2'], $configs[0]['analysis_type_ids']);
        $this->assertSame(['el-1', 'el-2'], $configs[0]['parameter_keys']);
    }

    public function test_build_detail_plans_includes_all_analysis_type_ids(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config = $service->syncAnalysisTypeIdsOnConfig($config, ['at-1', 'at-2']);
        $config['sample_type_id'] = 'st-1';
        $config['parameter_keys'] = ['el-1', 'el-2'];

        $plans = $service->buildDetailPlansFromConfigs([$config]);

        $this->assertCount(1, $plans);
        $this->assertSame(['at-1', 'at-2'], $plans[0]['analysis_type_ids']);
    }

    public function test_validate_configs_requires_at_least_one_analysis_type(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['parameter_keys'] = ['el-1'];

        $this->expectException(ValidationException::class);

        $service->validateConfigs([$config]);
    }

    public function test_resolve_single_element_id_matches_analyte_code(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Code Resolve '.Str::random(6),
            'code' => 'FOOD-CR-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'General Foods Code '.Str::random(6),
            'code' => 'GF-CR-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'SALMONELLA-'.Str::upper(Str::random(4)),
            'name' => 'Salmonella Code '.Str::random(6),
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $service = app(AcceptanceFormSampleConfigService::class);

        $this->assertSame(
            (string) $element->id,
            $service->resolveSingleElementId((string) $analyte->code, (string) $analysisType->id),
        );
    }

    public function test_apply_requested_parameter_keys_resolves_analysis_key_code(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Key Code '.Str::random(6),
            'code' => 'FOOD-KC-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'General Foods Key '.Str::random(6),
            'code' => 'GF-KC-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $analyteCode = 'SALM-'.Str::upper(Str::random(4));
        $analyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => $analyteCode,
            'name' => 'Salmonella Key '.Str::random(6),
            'active' => 1,
        ]);

        $element = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $analyte->id,
            'active' => 1,
        ]);

        $enquiry = SampleSubmissionRequest::query()->create([
            'crm_customer_id' => (string) Str::uuid(),
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => 'portal',
            'sample_lines' => [[
                'sample_type_id' => (string) $sampleType->id,
                'analysis_type_id' => (string) $analysisType->id,
                'number_of_samples' => 1,
            ]],
        ]);

        SampleSubmissionRequestRequestedAnalysis::query()->create([
            'sample_submission_request_id' => $enquiry->id,
            'sample_type_id' => (string) $sampleType->id,
            'analysis_type_id' => (string) $analysisType->id,
            'analysis_element_id' => null,
            'analysis_key' => $analyteCode,
            'analysis_label' => $analyteCode,
            'number_of_samples' => 1,
        ]);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = (string) $sampleType->id;
        $config['analysis_type_id'] = (string) $analysisType->id;
        $config['parameter_keys'] = [];

        $updated = $service->applyRequestedParameterKeysFromEnquiry(
            [$config],
            $enquiry->fresh(['requestedAnalyses']),
        );

        $this->assertSame([(string) $element->id], $updated[0]['parameter_keys']);
    }

    public function test_align_prefill_keeps_trf_element_missing_from_pricelist(): void
    {
        $sampleType = SampleType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'Food Align Keep '.Str::random(6),
            'code' => 'FOOD-AK-'.Str::upper(Str::random(4)),
            'active' => 1,
        ]);

        $analysisType = AnalysisType::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'General Foods Align '.Str::random(6),
            'code' => 'GF-AK-'.Str::upper(Str::random(4)),
            'sample_type_id' => $sampleType->id,
            'active' => 1,
        ]);

        $pricedAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'PRICED-'.Str::upper(Str::random(4)),
            'name' => 'Priced Param '.Str::random(6),
            'active' => 1,
        ]);

        $trfAnalyte = Analyte::query()->create([
            'id' => (string) Str::uuid(),
            'code' => 'SALM-AK-'.Str::upper(Str::random(4)),
            'name' => 'Salmonella Align '.Str::random(6),
            'active' => 1,
        ]);

        $pricedElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $pricedAnalyte->id,
            'active' => 1,
        ]);

        $trfElement = AnalysisElements::query()->create([
            'id' => (string) Str::uuid(),
            'analysis_type_id' => $analysisType->id,
            'analyte_id' => $trfAnalyte->id,
            'active' => 1,
        ]);

        $pricing = Mockery::mock(AcceptanceFormPricingService::class);
        $pricing->shouldReceive('parametersForAddLineSelection')
            ->andReturn([[
                'id' => (string) $pricedElement->id,
                'analysis_element_id' => (string) $pricedElement->id,
                'analysis_type_id' => (string) $analysisType->id,
                'sample_type_id' => (string) $sampleType->id,
                'code' => (string) $pricedAnalyte->code,
                'label' => (string) $pricedAnalyte->name,
                'unit_amount' => 10.0,
            ]]);
        $this->app->instance(AcceptanceFormPricingService::class, $pricing);

        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = (string) $sampleType->id;
        $config['analysis_type_id'] = (string) $analysisType->id;
        $config['parameter_keys'] = [(string) $trfElement->id];

        $aligned = $service->alignPrefillParameterKeysForConfig($config, (string) Str::uuid());

        $this->assertSame([(string) $trfElement->id], $aligned['parameter_keys']);

        $available = $service->parametersForConfig(
            (string) Str::uuid(),
            (string) $sampleType->id,
            (string) $analysisType->id,
        );
        $availableIds = collect($available)
            ->map(fn (array $param): string => (string) ($param['analysis_element_id'] ?? $param['id'] ?? ''))
            ->all();

        $this->assertContains((string) $trfElement->id, $availableIds);
        $this->assertContains((string) $pricedElement->id, $availableIds);
    }
}
