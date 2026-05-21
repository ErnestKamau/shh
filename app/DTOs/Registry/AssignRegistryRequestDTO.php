<?php

namespace App\DTOs\Registry;

readonly class AssignRegistryRequestDTO
{
    public function __construct(
        public string $registryRequestId,
        public int $assignedTo,
        public ?string $roleContext = null,
        public ?int $assignedBy = null,
    ) {
    }
}
