<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class SubmissionSummaryDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly int $totalSubmissions,
        public readonly int $awaitingSamples,
        public readonly int $inLabWorkflow,
        public readonly int $moreInfoRequested,
        public readonly int $completed,
    ) {}
}
