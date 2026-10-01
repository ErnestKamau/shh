<?php

namespace App\DTOs\Commercial;

use App\Enums\Commercial\SampleHeaderPoStatus;

/**
 * Result of checking a job's samples against its PO: which job keeps the covered samples
 * and which (if any) holds the rest as Awaiting PO.
 */
final class JobCoverageOutcome
{
    public function __construct(
        public readonly string $sampleHeaderId,
        public readonly SampleHeaderPoStatus $status,
        public readonly int $coveredSamples,
        public readonly int $heldSamples,
        public readonly ?string $heldSampleHeaderId = null,
        public readonly ?string $purchaseOrderId = null,
        public readonly ?string $purchaseOrderNumber = null,
    ) {}

    public function wasSplit(): bool
    {
        return $this->heldSampleHeaderId !== null;
    }

    public function isHeldWhole(): bool
    {
        return $this->status === SampleHeaderPoStatus::AwaitingPo;
    }
}
