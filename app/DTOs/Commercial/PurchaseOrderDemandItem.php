<?php

namespace App\DTOs\Commercial;

use InvalidArgumentException;

/**
 * Samples asking for PO cover: N samples of one package / analysis set for a sample type,
 * or N samples of one parameter when the PO prices that parameter per test.
 */
final class PurchaseOrderDemandItem
{
    /** @var list<string> */
    public readonly array $analysisTypeIds;

    /** @var list<string> Empty for package / analysis-type demand; the parameter for per-test demand. */
    public readonly array $analysisElementIds;

    /**
     * @param  string  $key  Caller-defined identifier used to read the allocation back (e.g. acceptance line group).
     * @param  iterable<int, mixed>  $analysisTypeIds
     * @param  iterable<int, mixed>  $analysisElementIds
     */
    public function __construct(
        public readonly string $key,
        public readonly ?string $sampleTypeId,
        iterable $analysisTypeIds,
        public readonly int $quantity,
        iterable $analysisElementIds = [],
    ) {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Demand quantity cannot be negative.');
        }

        $this->analysisTypeIds = self::normaliseIds($analysisTypeIds);
        $this->analysisElementIds = self::normaliseIds($analysisElementIds);
    }

    public function isPerTest(): bool
    {
        return $this->analysisElementIds !== [];
    }

    /**
     * @param  iterable<int, mixed>  $ids
     * @return list<string>
     */
    private static function normaliseIds(iterable $ids): array
    {
        $normalised = [];
        foreach ($ids as $id) {
            $id = trim((string) $id);
            if ($id !== '') {
                $normalised[$id] = $id;
            }
        }

        return array_values($normalised);
    }
}
