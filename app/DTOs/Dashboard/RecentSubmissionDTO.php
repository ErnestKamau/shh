<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class RecentSubmissionDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $requestNumber,
        public readonly ?string $requestType,
        public readonly ?string $submittedDate,
        public readonly ?string $workflowStage,
        public readonly string $status,
        public readonly ?string $priority,
        public readonly ?string $sampleStatus,
    ) {}
}
