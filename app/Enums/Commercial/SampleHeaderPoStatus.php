<?php

namespace App\Enums\Commercial;

enum SampleHeaderPoStatus: string
{
    case Covered = 'covered';
    case AwaitingPo = 'awaiting_po';
    case NotRequired = 'not_required';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Covered => 'Covered',
            self::AwaitingPo => 'Awaiting PO',
            self::NotRequired => 'No PO required',
            self::Cancelled => 'Cancelled (no PO)',
        };
    }
}
