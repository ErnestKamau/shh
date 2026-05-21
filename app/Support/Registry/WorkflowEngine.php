<?php

namespace App\Support\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\WorkflowStep;
use App\Models\Registry\WorkflowTransition;

class WorkflowEngine
{
    public function findTransition(
        RegistryRequest $request,
        WorkflowStep $fromStep,
        string $actionName
    ): ?WorkflowTransition {
        return WorkflowTransition::query()
            ->where('workflow_definition_id', $request->workflow_definition_id)
            ->where('from_step_id', $fromStep->id)
            ->where('action_name', $actionName)
            ->with('toStep')
            ->first();
    }

    public function canTransition(RegistryRequest $request, string $actionName): bool
    {
        $fromStep = app(RegistryWorkflowResolver::class)->currentStep($request);

        if ($fromStep === null) {
            return false;
        }

        return $this->findTransition($request, $fromStep, $actionName) !== null;
    }
}
