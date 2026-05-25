<?php

namespace App\Services\Equipment\Depreciation;

use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\Concerns\BuildsDepreciationPeriods;

class StraightLineCalculator implements DepreciationCalculatorContract
{
    use BuildsDepreciationPeriods;

    public function generateSchedule(EquipmentDepreciationConfig $config): array
    {
        $periods = $this->totalPeriods($config);
        $base = $config->depreciableBase();
        $perPeriod = $periods > 0 ? $base / $periods : 0;
        $amounts = array_fill(0, $periods, $perPeriod);

        return $this->buildScheduleRows($config, $amounts);
    }
}
