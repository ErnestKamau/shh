<?php

namespace App\Repositories\Registry;

use App\Models\Registry\WorkflowDefinition;
use Illuminate\Database\Eloquent\Collection;

class WorkflowRepository
{
    public function getActiveDefinitions(): Collection
    {
        return WorkflowDefinition::query()
            ->forCompany()
            ->active()
            ->with(['steps', 'transitions.fromStep', 'transitions.toStep'])
            ->orderBy('name')
            ->get();
    }

    public function findWithSteps(string $id): ?WorkflowDefinition
    {
        return WorkflowDefinition::query()
            ->with(['steps', 'transitions'])
            ->find($id);
    }
}
