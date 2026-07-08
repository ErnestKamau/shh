<?php

namespace App\Livewire\Registry;

use App\Actions\Registry\AssignRegistryRequestAction;
use App\DTOs\Registry\AssignRegistryRequestDTO;
use App\Models\Registry\RegistryRequest;
use App\User;
use Livewire\Component;

class RegistryAssignmentPanel extends Component
{
    public string $requestId;

    public ?string $assigned_to = null;

    public string $role_context = '';

    public function mount(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function assign(AssignRegistryRequestAction $action): void
    {
        $this->validate([
            'assigned_to' => ['required', 'uuid', 'exists:users,id'],
            'role_context' => ['nullable', 'string', 'max:100'],
        ]);

        $action->execute(new AssignRegistryRequestDTO(
            registryRequestId: $this->requestId,
            assignedTo: (string) $this->assigned_to,
            roleContext: $this->role_context ?: null,
        ));

        session()->flash('success', 'Assignment updated.');
        $this->dispatch('assignment-updated');
    }

    public function render()
    {
        $request = RegistryRequest::query()
            ->with(['assignments.assignee', 'assignee'])
            ->findOrFail($this->requestId);

        $users = User::query()->orderBy('name')->limit(200)->get(['id', 'name']);

        return view('livewire.registry.registry-assignment-panel', compact('request', 'users'));
    }
}
