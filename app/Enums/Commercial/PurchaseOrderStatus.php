<?php

namespace App\Enums\Commercial;

enum PurchaseOrderStatus: string
{
    case Active = 'active';
    case Exhausted = 'exhausted';
    case Expired = 'expired';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * Statuses whose remaining quantity may still be drawn from.
     */
    public function acceptsDraws(): bool
    {
        return $this === self::Active;
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Exhausted => 'Exhausted',
            self::Expired => 'Expired',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }
}
