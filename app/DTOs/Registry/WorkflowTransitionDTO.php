<?php

namespace App\DTOs\Registry;

readonly class WorkflowTransitionDTO
{
    public function __construct(
        public string $registryRequestId,
        public string $actionName,
        public ?string $comment = null,
        public ?string $performedBy = null,
    ) {
    }
}
