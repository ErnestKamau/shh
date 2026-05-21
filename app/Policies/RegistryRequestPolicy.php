<?php

namespace App\Policies;

use App\Models\Registry\RegistryRequest;
use App\User;

class RegistryRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('registry.components.requests.view');
    }

    public function view(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.requests.view');
    }

    public function create(User $user): bool
    {
        return $user->can('registry.components.requests.add');
    }

    public function update(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.requests.edit');
    }

    public function delete(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.requests.delete');
    }

    public function approve(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.approval queue.edit');
    }

    public function assign(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.assignments.edit');
    }

    public function uploadDocument(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.documents.add');
    }

    public function downloadDocument(User $user, RegistryRequest $request): bool
    {
        return $user->can('registry.components.documents.view');
    }
}
