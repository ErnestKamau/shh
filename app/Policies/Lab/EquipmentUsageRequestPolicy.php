<?php

namespace App\Policies\Lab;

use App\Models\Lab\EquipmentUsageRequest;
use App\Services\Lab\UserZoneResolver;
use App\User;

class EquipmentUsageRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('laboratory.components.equipment-requests.view');
    }

    public function view(User $user, EquipmentUsageRequest $request): bool
    {
        if (! $user->can('laboratory.components.equipment-requests.view')) {
            return false;
        }

        if ($user->can('laboratory.components.equipment-requests.approve')) {
            return true;
        }

        if ((string) $request->requester_id === (string) $user->id) {
            return true;
        }

        return app(UserZoneResolver::class)->userHasZone($user, $request->zone_id);
    }

    public function create(User $user): bool
    {
        return $user->can('laboratory.components.equipment-requests.add');
    }

    public function approve(User $user, EquipmentUsageRequest $request): bool
    {
        return $user->can('laboratory.components.equipment-requests.approve')
            && $request->isPending();
    }

    public function cancel(User $user, EquipmentUsageRequest $request): bool
    {
        return $request->isPending()
            && (string) $request->requester_id === (string) $user->id;
    }
}
