<?php

namespace App\DTOs\Commercial;

use InvalidArgumentException;

/**
 * Samples asking for PO cover: N samples of one package / analysis set for a sample type.
 */
final class PurchaseOrderDemandItem
{
    /** @var list<string> */
    public readonly array $analysisTypeIds;

    /**
     * @param  string  $key  Caller-defined identifier used to read the allocation back (e.g. acceptance line group).
     * @param  iterable<int, mixed>  $analysisTypeIds
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $sampleTypeId,
        iterable $analysisTypeIds,
        public readonly int $quantity,
    ) {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Demand quantity cannot be negative.');
        }

        $ids = [];
        foreach ($analysisTypeIds as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $ids[$id] = $id;
            }
        }

        $this->analysisTypeIds = array_values($ids);
    }
}
