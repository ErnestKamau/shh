<?php

namespace App\Services\Equipment\Depreciation;

use App\Enums\Equipment\DepreciationFrequency;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Services\Equipment\Depreciation\Concerns\BuildsDepreciationPeriods;

class SumOfYearsDigitsCalculator implements DepreciationCalculatorContract
{
    use BuildsDepreciationPeriods;

    public function generateSchedule(EquipmentDepreciationConfig $config): array
    {
        $years = max(1, (int) $config->useful_life_years);
        $sumOfYears = $years * ($years + 1) / 2;
        $base = $config->depreciableBase();
        $frequency = $config->frequency instanceof DepreciationFrequency
            ? $config->frequency
            : DepreciationFrequency::from((string) $config->frequency);
        $periodsPerYear = $frequency->periodsPerYear();
        $totalPeriods = $years * $periodsPerYear;

        $yearlyAmounts = [];
        for ($year = 1; $year <= $years; $year++) {
            $remainingLife = $years - $year + 1;
            $yearlyAmounts[$year] = ($remainingLife / $sumOfYears) * $base;
        }

        $amounts = [];
        for ($year = 1; $year <= $years; $year++) {
            $perPeriod = $yearlyAmounts[$year] / $periodsPerYear;
            for ($p = 0; $p < $periodsPerYear; $p++) {
                $amounts[] = $perPeriod;
            }
        }

        $amounts = array_slice($amounts, 0, $totalPeriods);

        return $this->buildScheduleRows($config, $amounts);
    }
}
