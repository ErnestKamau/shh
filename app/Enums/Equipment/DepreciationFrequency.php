<?php

namespace App\Enums\Equipment;

enum DepreciationFrequency: string
{
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function periodsPerYear(): int
    {
        return match ($this) {
            self::Monthly => 12,
            self::Quarterly => 4,
            self::Yearly => 1,
        };
    }
}
