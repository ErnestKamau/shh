<?php

namespace App\DTOs\Registry;

readonly class AssignRegistryRequestDTO
{
    public function __construct(
        public string $registryRequestId,
        public string $assignedTo,
        public ?string $roleContext = null,
        public ?string $assignedBy = null,
    ) {
    }
}
