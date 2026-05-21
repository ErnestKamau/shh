<?php

namespace App\Actions\Registry;

use App\DTOs\Registry\WorkflowTransitionDTO;
use App\Models\Registry\RegistryRequest;

class RejectRegistryRequestAction
{
    public function __construct(
        protected TransitionWorkflowAction $transitionWorkflowAction,
    ) {
    }

    public function execute(string $registryRequestId, ?string $comment = null, ?int $performedBy = null): RegistryRequest
    {
        return $this->transitionWorkflowAction->execute(new WorkflowTransitionDTO(
            registryRequestId: $registryRequestId,
            actionName: 'reject',
            comment: $comment,
            performedBy: $performedBy,
        ));
    }
}
