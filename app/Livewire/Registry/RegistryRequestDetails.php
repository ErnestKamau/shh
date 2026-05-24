<?php

namespace App\Livewire\Registry;

use App\Models\Registry\RegistryRequest;
use Livewire\Component;

class RegistryRequestDetails extends Component
{
    public string $requestId;

    public function mount(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function render()
    {
        $request = RegistryRequest::query()
            ->forCompany()
            ->with(['category', 'assignee', 'assignments', 'documents', 'actions.performer', 'statusLogs'])
            ->findOrFail($this->requestId);

        return view('livewire.registry.registry-request-details', compact('request'));
    }
}
