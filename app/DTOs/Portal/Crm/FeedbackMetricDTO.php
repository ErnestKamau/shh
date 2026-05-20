<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class FeedbackMetricDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $maxRating,
        public readonly int $sortOrder,
        public readonly ?string $promptText = null,
    ) {}
}
