<?php

namespace App\Policies;

use App\Models\Equipments\EquipmentDisposal;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentDisposalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('equipment.components.equipment-disposal.view');
    }

    public function view(User $user, EquipmentDisposal $equipmentDisposal): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('equipment.components.equipment-disposal.add');
    }

    public function update(User $user, EquipmentDisposal $equipmentDisposal): bool
    {
        return $user->hasPermissionTo('equipment.components.equipment-disposal.edit');
    }

    public function delete(User $user, EquipmentDisposal $equipmentDisposal): bool
    {
        return $user->hasPermissionTo('equipment.components.equipment-disposal.delete');
    }
}
