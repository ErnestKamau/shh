<?php

namespace App\DTOs\Commercial;

/**
 * Outcome of allocating one demand item against a PO.
 */
final class PurchaseOrderAllocationLine
{
    public const REASON_NO_MATCHING_LINE = 'no_matching_line';

    public const REASON_PO_NOT_ACTIVE = 'po_not_active';

    public const REASON_OUTSIDE_VALIDITY = 'outside_validity';

    public const REASON_INSUFFICIENT_BALANCE = 'insufficient_balance';

    public function __construct(
        public readonly string $demandKey,
        public readonly ?string $lineId,
        public readonly int $requested,
        public readonly int $covered,
        public readonly ?string $uncoveredReason = null,
    ) {}

    public function uncovered(): int
    {
        return max(0, $this->requested - $this->covered);
    }
}
