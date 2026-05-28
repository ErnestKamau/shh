<?php

namespace App\Services\Equipment\Depreciation\Concerns;

use App\Enums\Equipment\DepreciationFrequency;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use Carbon\Carbon;

trait BuildsDepreciationPeriods
{
    protected function totalPeriods(EquipmentDepreciationConfig $config): int
    {
        $years = max(1, (int) $config->useful_life_years);
        $frequency = $config->frequency instanceof DepreciationFrequency
            ? $config->frequency
            : DepreciationFrequency::from((string) $config->frequency);

        return $years * $frequency->periodsPerYear();
    }

    protected function periodDate(Carbon $start, DepreciationFrequency $frequency, int $index): Carbon
    {
        return match ($frequency) {
            DepreciationFrequency::Monthly => $start->copy()->addMonths($index),
            DepreciationFrequency::Quarterly => $start->copy()->addMonths($index * 3),
            DepreciationFrequency::Yearly => $start->copy()->addYears($index),
        };
    }

    protected function periodLabel(Carbon $date, DepreciationFrequency $frequency): string
    {
        return match ($frequency) {
            DepreciationFrequency::Monthly => $date->format('Y-m'),
            DepreciationFrequency::Quarterly => $date->format('Y') . '-Q' . (int) ceil($date->month / 3),
            DepreciationFrequency::Yearly => $date->format('Y'),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildScheduleRows(
        EquipmentDepreciationConfig $config,
        array $periodAmounts
    ): array {
        $start = Carbon::parse($config->depreciation_start_date)->startOfMonth();
        $frequency = $config->frequency instanceof DepreciationFrequency
            ? $config->frequency
            : DepreciationFrequency::from((string) $config->frequency);
        $salvage = (float) $config->salvage_value;
        $capitalized = (float) $config->capitalized_amount;
        $opening = $capitalized;
        $accumulated = 0.0;
        $rows = [];

        foreach ($periodAmounts as $index => $amount) {
            $amount = round(max(0, (float) $amount), 2);
            if ($opening <= $salvage + 0.001) {
                break;
            }

            $maxDepreciation = $opening - $salvage;
            $amount = min($amount, $maxDepreciation);
            $accumulated += $amount;
            $closing = round($opening - $amount, 2);

            $periodDate = $this->periodDate($start, $frequency, $index);

            $rows[] = [
                'period_label' => $this->periodLabel($periodDate, $frequency),
                'period_date' => $periodDate,
                'period_index' => $index,
                'opening_book_value' => round($opening, 2),
                'depreciation_amount' => $amount,
                'accumulated_depreciation' => round($accumulated, 2),
                'closing_book_value' => $closing,
            ];

            $opening = $closing;
        }

        return $rows;
    }
}
