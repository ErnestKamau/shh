<?php

namespace App\DTOs\Dashboard;

use App\DTOs\Dashboard\Concerns\ArrayableDto;

final class FeedbackDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?float $rating,
        public readonly ?string $comment,
        public readonly ?string $submittedAt,
    ) {}
}
