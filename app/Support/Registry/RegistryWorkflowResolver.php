<?php

namespace App\Support\Registry;

use App\Models\Registry\RegistryRequest;
use App\Models\Registry\RegistryRequestCategory;
use App\Models\Registry\WorkflowDefinition;
use App\Models\Registry\WorkflowStep;

class RegistryWorkflowResolver
{
    public function resolveDefinitionForCategory(RegistryRequestCategory $category): ?WorkflowDefinition
    {
        return $category->workflowDefinition;
    }

    public function firstStep(WorkflowDefinition $definition): ?WorkflowStep
    {
        return $definition->steps()->orderBy('sequence')->first();
    }

    public function stepByCode(WorkflowDefinition $definition, string $stepCode): ?WorkflowStep
    {
        return $definition->steps()->where('step_code', $stepCode)->first();
    }

    public function currentStep(RegistryRequest $request): ?WorkflowStep
    {
        if ($request->workflow_definition_id === null || $request->current_stage === null) {
            return null;
        }

        return WorkflowStep::query()
            ->where('workflow_definition_id', $request->workflow_definition_id)
            ->where('step_code', $request->current_stage)
            ->first();
    }
}
