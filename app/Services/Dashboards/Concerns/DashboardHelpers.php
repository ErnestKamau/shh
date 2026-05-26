<?php

namespace App\Services\Dashboards\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Shared helper methods used across multiple dashboard services.
 */
trait DashboardHelpers
{
    /**
     * Compute a signed TAT offset integer with a 12-hour grace window.
     *
     * Returns:
     *   > 0  late  (finished more than 12 h after the expected deadline)
     *   = 0  on-time (completed within ±12 h of the deadline)
     *   < 0  early (finished more than 12 h before the deadline)
     *
     * Keeping this as a public static method allows it to be called from
     * Observers, Controllers, and Service classes without circular deps.
     */
    public static function computeSignedTatOffset($expected, $actual): int
    {
        if (!$expected || !$actual) {
            return 0;
        }

        $expectedAt  = Carbon::parse($expected);
        $actualAt    = Carbon::parse($actual);
        $diffMinutes = $expectedAt->diffInMinutes($actualAt, false);
        $grace       = 12 * 60;   // 720 minutes
        $day         = 24 * 60;   // 1440 minutes

        if ($diffMinutes > $grace) {
            return (int) ceil(($diffMinutes - $grace) / $day);
        }
        if ($diffMinutes < -$grace) {
            return -1 * (int) ceil((abs($diffMinutes) - $grace) / $day);
        }

        return 0;
    }

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
