<?php

namespace Tests\Unit\Services;

use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AcceptanceFormSampleConfigServiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_build_configs_from_prefill_groups_by_sample_and_analysis_type(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'number_of_samples' => 1,
            ],
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-2',
                'analysis_element_id' => 'el-2',
                'number_of_samples' => 1,
            ],
        ]);

        $this->assertCount(2, $configs);
        $this->assertSame('st-1', $configs[0]['sample_type_id']);
        $this->assertSame('at-1', $configs[0]['analysis_type_id']);
        $this->assertSame(['el-1'], $configs[0]['parameter_keys']);
        $this->assertSame(1, $configs[0]['number_of_samples']);
        $this->assertCount(1, $configs[0]['instances']);
        $this->assertSame('at-2', $configs[1]['analysis_type_id']);
    }

    public function test_build_configs_increments_sample_count_per_prefill_line_in_same_bucket(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $configs = $service->buildConfigsFromPrefill([
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-1',
                'number_of_samples' => 1,
            ],
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-2',
                'number_of_samples' => 1,
            ],
            [
                'sample_type_id' => 'st-1',
                'analysis_type_id' => 'at-1',
                'analysis_element_id' => 'el-3',
                'number_of_samples' => 1,
            ],
        ]);

        $this->assertCount(1, $configs);
        $this->assertSame(3, $configs[0]['number_of_samples']);
        $this->assertCount(3, $configs[0]['instances']);
    }

    public function test_sync_instances_preserves_existing_values_when_count_increases(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $instances = $service->syncInstances([
            ['customer_sample_id' => 'CUST-1', 'sample_marking' => 'Mark A'],
        ], 3);

        $this->assertCount(3, $instances);
        $this->assertSame('CUST-1', $instances[0]['customer_sample_id']);
        $this->assertSame('Mark A', $instances[0]['sample_marking']);
        $this->assertSame('', $instances[2]['customer_sample_id']);
    }

    public function test_validate_configs_requires_parameters(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $this->expectException(ValidationException::class);

        $service->validateConfigs([
            $service->emptyConfig(),
        ]);
    }

    public function test_build_detail_plans_from_configs_creates_one_plan_per_instance(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);
        $config = $service->emptyConfig();
        $config['sample_type_id'] = 'st-1';
        $config['analysis_type_id'] = 'at-1';
        $config['parameter_keys'] = ['el-1', 'el-2'];
        $config['number_of_samples'] = 2;
        $config['instances'] = [
            ['customer_sample_id' => 'A-1', 'sample_marking' => 'M1'],
            ['customer_sample_id' => 'A-2', 'sample_marking' => 'M2'],
        ];

        $plans = $service->buildDetailPlansFromConfigs([$config]);

        $this->assertCount(2, $plans);
        $this->assertSame(['at-1'], $plans[0]['analysis_type_ids']);
        $this->assertSame(['el-1', 'el-2'], $plans[0]['analysis_element_ids']);
        $this->assertSame('A-1', $plans[0]['customer_sample_id']);
        $this->assertSame('M2', $plans[1]['sample_marking']);
    }

    public function test_resolve_lab_section_id_falls_back_to_analysis_elements(): void
    {
        $service = app(AcceptanceFormSampleConfigService::class);

        $existing = \App\AnalysisType::query()->whereNotNull('company_id')->whereNotNull('lab_id')->whereNotNull('sample_type_id')->first();
        $companyId = $existing?->company_id ?: (string) \Illuminate\Support\Str::uuid();
        $labId = $existing?->lab_id ?: (string) \Illuminate\Support\Str::uuid();
        $sampleTypeId = $existing?->sample_type_id ?: (string) \Illuminate\Support\Str::uuid();

        $stageId = (string) \Illuminate\Support\Str::uuid();

        \Illuminate\Support\Facades\DB::table('lab_sections')->insert([
            'id' => $stageId,
            'lab_id' => $labId,
            'company_id' => $companyId,
            'name' => 'Test Section',
            'code' => 'TST',
            'active' => true,
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
}
