<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class FeedbackSummaryDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly int $totalFeedbackSubmitted,
        public readonly ?float $averageRating,
        public readonly ?float $positiveFeedbackPercentage,
    ) {}
}
