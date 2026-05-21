<?php

namespace App\Actions\Registry;

use App\DTOs\Registry\ApproveRegistryRequestDTO;
use App\DTOs\Registry\WorkflowTransitionDTO;
use App\Models\Registry\RegistryRequest;

class ApproveRegistryRequestAction
{
    public function __construct(
        protected TransitionWorkflowAction $transitionWorkflowAction,
    ) {
    }

    public function execute(ApproveRegistryRequestDTO $dto): RegistryRequest
    {
        return $this->transitionWorkflowAction->execute(new WorkflowTransitionDTO(
            registryRequestId: $dto->registryRequestId,
            actionName: $dto->actionName,
            comment: $dto->comment,
            performedBy: $dto->performedBy,
        ));
    }
}
