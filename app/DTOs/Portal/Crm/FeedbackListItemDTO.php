<?php

namespace App\DTOs\Portal\Crm;

use App\DTOs\Portal\Crm\Concerns\ArrayableDto;

final class FeedbackListItemDTO
{
    use ArrayableDto;

    public function __construct(
        public readonly string $id,
        public readonly ?string $code,
        public readonly ?float $ratingOverall,
        public readonly ?string $specificFeedback,
        public readonly ?string $serviceType,
        public readonly ?string $serviceReferenceNo,
        public readonly ?string $submittedAt,
    ) {}
}
