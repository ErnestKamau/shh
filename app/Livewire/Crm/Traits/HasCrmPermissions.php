<?php

namespace App\Livewire\CRM\Traits;

trait HasCrmPermissions
{
    /**
     * Check if user has permission, abort if not
     * @param string $permissionKey Format: "CRM.components.Component-Name.Action"
     */
    protected function checkPermission($permissionKey)
    {
        if (!auth()->user()->can((string) $permissionKey)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /**
     * Check if user has permission, return boolean
     * @param string $permissionKey Format: "CRM.components.Component-Name.Action"
     * @return bool
     */
    protected function hasPermission($permissionKey)
    {
        return auth()->user()->can((string) $permissionKey);
    }
}

