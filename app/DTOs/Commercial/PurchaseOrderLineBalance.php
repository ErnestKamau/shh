<?php

namespace App\DTOs\Commercial;

use App\Models\Commercial\CustomerPurchaseOrderLine;

/**
 * PO line balances, either summed from the ledger or read from the cached columns.
 */
final class PurchaseOrderLineBalance
{
    public function __construct(
        public readonly int $ordered,
        public readonly int $reserved,
        public readonly int $committed,
        public readonly int $invoiced,
    ) {}

    public static function fromCachedColumns(CustomerPurchaseOrderLine $line): self
    {
        return new self(
            (int) $line->ordered_qty,
            (int) $line->reserved_qty,
            (int) $line->committed_qty,
            (int) $line->invoiced_qty,
        );
    }

    public function remaining(): int
    {
        return $this->ordered - $this->reserved - $this->committed;
    }

    public function equals(self $other): bool
    {
        return $this->ordered === $other->ordered
            && $this->reserved === $other->reserved
            && $this->committed === $other->committed
            && $this->invoiced === $other->invoiced;
    }

    /**
     * @return array{ordered_qty: int, reserved_qty: int, committed_qty: int, invoiced_qty: int, remaining_qty: int}
     */
    public function toColumns(): array
    {
        return [
            'ordered_qty' => $this->ordered,
            'reserved_qty' => $this->reserved,
            'committed_qty' => $this->committed,
            'invoiced_qty' => $this->invoiced,
            'remaining_qty' => $this->remaining(),
        ];
    }
}
