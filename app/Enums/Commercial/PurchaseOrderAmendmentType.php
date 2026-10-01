<?php

namespace App\Enums\Commercial;

enum PurchaseOrderAmendmentType: string
{
    case TopUp = 'top_up';
    case Reduce = 'reduce';
    case ExtendValidity = 'extend_validity';
    case AddLine = 'add_line';
    case UpdateDetails = 'update_details';
    case Close = 'close';
    case Cancel = 'cancel';

    public function label(): string
    {
        return match ($this) {
            self::TopUp => 'Top-up',
            self::Reduce => 'Reduction',
            self::ExtendValidity => 'Validity changed',
            self::AddLine => 'Line added',
            self::UpdateDetails => 'Details updated',
            self::Close => 'Closed',
            self::Cancel => 'Cancelled',
        };
    }
}
