<?php

namespace App\Actions\Registry;

use App\Models\Registry\RegistryRequest;
use App\Services\Registry\WorkflowEngineService;

class StartWorkflowAction
{
    public function __construct(
        protected WorkflowEngineService $workflowEngineService,
    ) {
    }

    public function execute(RegistryRequest $request): RegistryRequest
    {
        return $this->workflowEngineService->startWorkflow($request);
    }
}
