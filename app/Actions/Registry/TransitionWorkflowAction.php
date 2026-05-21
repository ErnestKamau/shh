<?php

namespace App\Actions\Registry;

use App\DTOs\Registry\WorkflowTransitionDTO;
use App\Events\Registry\RegistryRequestApproved;
use App\Events\Registry\RegistryRequestRejected;
use App\Models\Registry\RegistryRequest;
use App\Services\Registry\RegistryIntegrationService;
use App\Services\Registry\WorkflowEngineService;

class TransitionWorkflowAction
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
        protected RegistryIntegrationService $integrationService,
    ) {
    }

    public function execute(WorkflowTransitionDTO $dto): RegistryRequest
    {
        $request = $this->workflowEngineService->transition($dto);

        if ($dto->actionName === 'approve') {
            event(new RegistryRequestApproved($request));
            $this->integrationService->handleCategoryTransition($request->fresh(['category']));
        }

        if ($dto->actionName === 'reject') {
            $request->update(['status' => RegistryRequest::STATUS_RETURNED]);
            event(new RegistryRequestRejected($request->fresh()));
        }

        return $request->fresh(['category', 'actions']);
    }
}
