<?php

namespace Tests\Feature\Equipment;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Equipment;
use App\Services\Equipment\Depreciation\DepreciationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepreciationBookValueTest extends TestCase
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

    public function test_current_book_value_reflects_as_of_today_not_final_schedule_row(): void
    {
        $equipment = Equipment::query()->create([
            'name' => 'Depreciation Book Value Test',
            'equipment_number' => 'EQ-DEP-BV-001',
            'description' => 'Test',
            'make' => 'Make',
            'model' => 'Model',
            'purchase_price' => 650000,
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
            'freight_cost' => 50000,
            'depreciation_start_date' => now()->startOfMonth()->format('Y-m-d'),
            'useful_life_years' => 7,
            'salvage_value' => 150000,
            'frequencies' => ['monthly'],
            'currency' => 'KES',
        ]);

        $service->generateSchedule($config->fresh(['method']), 'test');

        $config->refresh();
        $version = $config->activeScheduleVersion;
        $this->assertNotNull($version);

        $lastSchedule = $version->schedules()
            ->where('frequency', 'monthly')
            ->orderByDesc('period_index')
            ->first();

        $this->assertNotNull($lastSchedule);
        $this->assertNotEqualsWithDelta(
            (float) $lastSchedule->closing_book_value,
            (float) $config->current_book_value,
            1.0,
            'Current book value must not be the final period closing value when depreciation recently started.'
        );

        $firstSchedule = $version->schedules()
            ->where('frequency', 'monthly')
            ->orderBy('period_index')
            ->first();

        $this->assertNotNull($firstSchedule);
        $this->assertEqualsWithDelta(
            (float) $firstSchedule->closing_book_value,
            (float) $config->current_book_value,
            0.02,
            'Current book value should match the as-of-today schedule period, not the terminal period.'
        );

        $this->assertGreaterThan(
            (float) $lastSchedule->closing_book_value,
            (float) $config->current_book_value
        );
    }
}
