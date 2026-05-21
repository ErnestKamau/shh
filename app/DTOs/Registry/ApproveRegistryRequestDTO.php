<?php

namespace App\DTOs\Registry;

readonly class ApproveRegistryRequestDTO
{
    public function __construct(
        public string $registryRequestId,
        public string $actionName = 'approve',
        public ?string $comment = null,
        public ?int $performedBy = null,
    ) {
    }
}
