<?php

namespace App\Livewire\Registry;

use App\Repositories\Registry\WorkflowRepository;
use Livewire\Component;

class WorkflowConfigManager extends Component
{
    public function render(WorkflowRepository $repository)
    {
        $definitions = $repository->getActiveDefinitions();

        return view('livewire.registry.workflow-config-manager', compact('definitions'));
    }
}
