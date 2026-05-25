<?php

namespace App\Services\Equipment\Depreciation;

use App\Enums\Equipment\DecliningBalanceType;
use App\Enums\Equipment\DepreciationFrequency;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\Concerns\BuildsDepreciationPeriods;

class DecliningBalanceCalculator implements DepreciationCalculatorContract
{
    use BuildsDepreciationPeriods;

    public function generateSchedule(EquipmentDepreciationConfig $config): array
    {
        $periods = $this->totalPeriods($config);
        $years = max(1, (int) $config->useful_life_years);
        $decliningType = $config->declining_balance_type instanceof DecliningBalanceType
            ? $config->declining_balance_type
            : DecliningBalanceType::from((string) ($config->declining_balance_type ?? 'standard'));

        $annualRate = $config->depreciation_rate
            ? (float) $config->depreciation_rate / 100
            : ($decliningType === DecliningBalanceType::Double ? 2 / $years : 1 / $years);

        if ($decliningType === DecliningBalanceType::Double && ! $config->depreciation_rate) {
            $annualRate = min(1, 2 / $years);
        }

        $frequency = $config->frequency instanceof DepreciationFrequency
            ? $config->frequency
            : DepreciationFrequency::from((string) $config->frequency);
        $periodRate = $annualRate / $frequency->periodsPerYear();

        $salvage = (float) $config->salvage_value;
        $bookValue = (float) $config->capitalized_amount;
        $amounts = [];

        for ($i = 0; $i < $periods; $i++) {
            if ($bookValue <= $salvage) {
                $amounts[] = 0;
                continue;
            }
            $depreciation = $bookValue * $periodRate;
            $amounts[] = $depreciation;
            $bookValue -= $depreciation;
        }

        return $this->buildScheduleRows($config, $amounts);
    }
}
