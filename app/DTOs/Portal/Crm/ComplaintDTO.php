<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class ComplaintDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?string $complaintNumber,
        public readonly ?string $title,
        public readonly ?string $complaintType,
        public readonly ?string $status,
        public readonly ?string $createdAt,
        public readonly string $resolutionStatus,
        public readonly ?string $priority = null,
        public readonly ?string $stageLabel = null,
    ) {}
}
