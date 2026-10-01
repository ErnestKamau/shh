<?php

namespace App\DTOs\Commercial;

final class PurchaseOrderAllocationResult
{
    /**
     * @param  list<PurchaseOrderAllocationLine>  $lines
     */
    public function __construct(
        public readonly ?string $purchaseOrderId,
        public readonly array $lines,
    ) {}

    public function totalRequested(): int
    {
        return array_sum(array_map(static fn (PurchaseOrderAllocationLine $line): int => $line->requested, $this->lines));
    }

    public function totalCovered(): int
    {
        return array_sum(array_map(static fn (PurchaseOrderAllocationLine $line): int => $line->covered, $this->lines));
    }

    public function totalUncovered(): int
    {
        return array_sum(array_map(static fn (PurchaseOrderAllocationLine $line): int => $line->uncovered(), $this->lines));
    }

    public function hasUncovered(): bool
    {
        return $this->totalUncovered() > 0;
    }

    public function forDemand(string $demandKey): ?PurchaseOrderAllocationLine
    {
        foreach ($this->lines as $line) {
            if ($line->demandKey === $demandKey) {
                return $line;
            }
        }

        return null;
    }
}
