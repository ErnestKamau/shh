<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class CreatedComplaintDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly string $complaintNumber,
        public readonly string $status,
        public readonly string $resolutionStatus,
        public readonly ?string $createdAt,
    ) {}
}
