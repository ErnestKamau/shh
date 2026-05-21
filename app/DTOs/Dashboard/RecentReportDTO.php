<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class RecentReportDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly ?string $reportNumber,
        public readonly ?string $submissionRequestNumber,
        public readonly ?string $releasedDate,
        public readonly ?string $reportType,
        public readonly ?string $downloadUrl,
        public readonly string $releaseStatus,
    ) {}
}
