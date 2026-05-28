<?php

namespace App\Services\Equipment\Depreciation;

use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\Concerns\BuildsDepreciationPeriods;

class UnitsOfProductionCalculator implements DepreciationCalculatorContract
{
    use BuildsDepreciationPeriods;

    public function generateSchedule(EquipmentDepreciationConfig $config): array
    {
        $expectedUnits = max(1, (float) $config->expected_total_units);
        $base = $config->depreciableBase();
        $perUnit = $base / $expectedUnits;
        $periods = $this->totalPeriods($config);
        $unitsPerPeriod = $expectedUnits / $periods;
        $amounts = [];

        for ($i = 0; $i < $periods; $i++) {
            $amounts[] = $unitsPerPeriod * $perUnit;
        }

        return $this->buildScheduleRows($config, $amounts);
    }
}
