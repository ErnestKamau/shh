<?php

namespace Tests\Feature\Equipment;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Jobs\Equipment\GenerateDepreciationScheduleJob;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Equipment;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class DepreciationConfigTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DepreciationMethod::query()->create([
            'code' => DepreciationMethodCode::StraightLine->value,
            'name' => 'Straight-Line',
            'is_active' => true,
        ]);
    }

    public function test_upsert_config_dispatches_generation_job(): void
    {
        Bus::fake();

        $equipment = Equipment::query()->create([
            'name' => 'Test Equipment',
            'equipment_number' => 'EQ-DEP-001',
            'description' => 'Test',
            'make' => 'Make',
            'model' => 'Model',
            'purchase_price' => 5000,
            'status' => 'Active',
            'condition' => 'Good',
            'assigned_department' => '1',
            'warranty_date' => now(),
            'maintainance_days' => 30,
            'maintainance_notification_in_days' => 7,
            'calibration_days' => 365,
            'calibration_notification_in_days' => 30,
            'active' => true,
        ]);

        $method = DepreciationMethod::query()->first();

        $service = app(DepreciationService::class);
        $config = $service->upsertConfig($equipment, [
            'enable_depreciation' => true,
            'depreciation_method_id' => $method->id,
            'freight_cost' => 500,
            'depreciation_start_date' => now()->format('Y-m-d'),
            'useful_life_years' => 5,
            'salvage_value' => 500,
            'frequencies' => ['monthly', 'quarterly'],
            'currency' => 'USD',
        ]);

        $this->assertEquals(5500.0, (float) $config->capitalized_amount);
        $this->assertTrue($config->enable_depreciation);
        $this->assertEquals(['monthly', 'quarterly'], $config->resolvedFrequencies());
        $this->assertEquals('monthly', $config->frequency->value ?? $config->frequency);

        GenerateDepreciationScheduleJob::dispatch($config->id);
        Bus::assertDispatched(GenerateDepreciationScheduleJob::class);
    }
}
