<?php

namespace App\Services\Dashboards\Concerns;

use Illuminate\Support\Collection;

/**
 * Shared helper methods used across multiple dashboard services.
 */
trait DashboardHelpers
{
    protected function toFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    protected function weightedAverage(Collection $rows, string $valueKey, string $weightKey): ?float
    {
        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($rows as $row) {
            $value = data_get($row, $valueKey);
            $weight = data_get($row, $weightKey);

            if ($value === null || $weight === null || (float) $weight <= 0) {
                continue;
            }

            $weightedSum += ((float) $value * (float) $weight);
            $totalWeight += (float) $weight;
        }

        if ($totalWeight === 0.0) {
            return null;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Map dynamic status strings to localized keys.
     */
    public function translateStatus(?string $status): string
    {
        if (!$status) return '';
        
        $slug = strtolower(str_replace(' ', '_', $status));
        $key = "mas/lab.status_{$slug}";
        $translated = __($key);

        return $translated === $key ? $status : $translated;
    }

    protected function resolveDepartmentName($value, Collection $departmentNames): string
    {
        if ($value === null || $value === '') {
            return 'Unassigned';
        }

        if (is_numeric($value) && isset($departmentNames[(int) $value])) {
            return $departmentNames[(int) $value];
        }

        return (string) $value;
    }

    protected function repositoryConnection(): string
    {
        return 'pgsql';
    }

    protected function reportingSchema(): string
    {
        return 'public';
    }
}
