<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class FeedbackDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?string $code,
        public readonly ?float $ratingOverall,
        public readonly ?string $submittedAt,
    ) {}
}
