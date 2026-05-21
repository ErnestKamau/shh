<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use Livewire\Component;

class RegistryWorkflowTimeline extends Component
{
    public string $requestId;

    public function mount(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function render()
    {
        $request = RegistryRequest::query()
            ->with(['actions.performer', 'statusLogs', 'workflowDefinition.steps'])
            ->findOrFail($this->requestId);

        return view('livewire.registry.registry-workflow-timeline', compact('request'));
    }
}
