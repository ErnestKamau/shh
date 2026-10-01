<?php

namespace App\Enums\Commercial;

enum PurchaseOrderInvoicingMode: string
{
    case PerJob = 'per_job';
    case Periodic = 'periodic';

    public function label(): string
    {
        return match ($this) {
            self::PerJob => 'Per job',
            self::Periodic => 'Periodic',
        };
    }
}
