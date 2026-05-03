<?php

namespace App\Policies;

use App\Models\RiskManagement\Risk;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RiskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any risks.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('risk-management.components.risks.view')
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the risk.
     */
    public function view(User $user, Risk $risk): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create risks.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('risk-management.components.risks.add')
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the risk.
     */
    public function update(User $user, Risk $risk): bool
    {
        return $user->hasPermissionTo('risk-management.components.risks.edit')
            || $user->hasRole('admin');
    }

    /**
     * Determine whether the user can delete the risk.
     */
    public function delete(User $user, Risk $risk): bool
    {
        return $user->hasPermissionTo('risk-management.components.risks.delete')
            || $user->hasRole('admin');
    }
}