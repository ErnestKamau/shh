<?php

namespace App\Enums\Commercial;

enum PurchaseOrderType: string
{
    case Single = 'single';
    case Blanket = 'blanket';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single enquiry',
            self::Blanket => 'Blanket',
        };
    }
}
