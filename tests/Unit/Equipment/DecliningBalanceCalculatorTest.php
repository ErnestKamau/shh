<?php

namespace Tests\Unit\Equipment;

use App\Enums\Equipment\DecliningBalanceType;
use App\Enums\Equipment\DepreciationFrequency;
use App\Enums\Equipment\DepreciationMethodCode;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\DecliningBalanceCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecliningBalanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_loads_depreciation(): void
    {
        $method = DepreciationMethod::query()->create([
            'code' => DepreciationMethodCode::DecliningBalance->value,
            'name' => 'Declining Balance',
            'is_active' => true,
        ]);

        $config = new EquipmentDepreciationConfig([
            'depreciation_method_id' => $method->id,
            'capitalized_amount' => 10000,
            'salvage_value' => 0,
            'useful_life_years' => 5,
            'frequency' => DepreciationFrequency::Yearly,
            'depreciation_rate' => 40,
            'declining_balance_type' => DecliningBalanceType::Standard,
            'depreciation_start_date' => Carbon::parse('2024-01-01'),
        ]);
        $config->setRelation('method', $method);

        $schedule = (new DecliningBalanceCalculator)->generateSchedule($config);

        $this->assertGreaterThan(
            $schedule[1]['depreciation_amount'],
            $schedule[0]['depreciation_amount']
        );
    }
}
