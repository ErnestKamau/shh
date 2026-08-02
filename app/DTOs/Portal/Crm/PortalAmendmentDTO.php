<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class PortalAmendmentDTO
{
    use ArrayableDto;

    /**
     * @param  list<string>  $sampleCodes
     */
    public function __construct(
        public readonly string $id,
        public readonly string $reference,
        public readonly ?string $reportNumber,
        public readonly string $batchId,
        public readonly int $version,
        public readonly string $reason,
        public readonly string $status,
        public readonly array $sampleCodes,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}
}
