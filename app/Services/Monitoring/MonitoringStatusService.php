<?php

namespace App\Services\Monitoring;

class MonitoringStatusService
{
    public function statusFromRange(?float $value, ?float $min, ?float $max, ?float $warningMargin = null): string
    {
        if ($value === null || $min === null || $max === null) {
            return 'PENDING';
        }

        if ($value < $min || $value > $max) {
            return 'OUT OF RANGE';
        }

        if ($warningMargin === null || $warningMargin <= 0) {
            return 'IN RANGE';
        }

        $distanceToMin = abs($value - $min);
        $distanceToMax = abs($max - $value);

        if ($distanceToMin <= $warningMargin || $distanceToMax <= $warningMargin) {
            return 'WARNING';
        }

        return 'IN RANGE';
    }

    public function aggregate(array $statuses): string
    {
        $normalized = array_map(static fn ($status): string => strtoupper((string) $status), $statuses);

        if (in_array('CRITICAL', $normalized, true)) {
            return 'CRITICAL';
        }

        if (in_array('OUT OF RANGE', $normalized, true) || in_array('FAIL', $normalized, true)) {
            return 'OUT OF RANGE';
        }

        if (in_array('WARNING', $normalized, true)) {
            return 'WARNING';
        }

        return 'IN RANGE';
    }
}
