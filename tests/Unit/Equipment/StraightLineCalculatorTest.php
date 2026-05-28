<?php

namespace Tests\Unit\Equipment;

use App\Enums\Equipment\DepreciationFrequency;
use App\Enums\Equipment\DepreciationMethodCode;
use App\Enums\Equipment\DepreciationStatus;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\StraightLineCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StraightLineCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_equal_period_amounts(): void
    {
        $method = DepreciationMethod::query()->create([
            'code' => DepreciationMethodCode::StraightLine->value,
            'name' => 'Straight-Line',
            'is_active' => true,
        ]);

        $config = new EquipmentDepreciationConfig([
            'depreciation_method_id' => $method->id,
            'enable_depreciation' => true,
            'capitalized_amount' => 10000,
            'salvage_value' => 1000,
            'useful_life_years' => 5,
            'frequency' => DepreciationFrequency::Yearly,
            'depreciation_start_date' => Carbon::parse('2024-01-01'),
            'status' => DepreciationStatus::Active,
        ]);
        $config->setRelation('method', $method);

        $calculator = new StraightLineCalculator;
        $schedule = $calculator->generateSchedule($config);

        $this->assertCount(5, $schedule);
        $this->assertEqualsWithDelta(1800.0, $schedule[0]['depreciation_amount'], 0.01);
        $this->assertEqualsWithDelta(1000.0, $schedule[4]['closing_book_value'], 0.01);
    }
}
