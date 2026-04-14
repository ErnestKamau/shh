<?php

namespace App\Livewire\CRM\Traits;

trait HasCrmPermissions
{
    protected function isAdminUser(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return true;
        }

        return $user->hasRole('admin');
    }

    /**
     * Check if user has permission, abort if not
     * @param string $permissionKey Format: "CRM.components.Component-Name.Action"
     */
    protected function checkPermission($permissionKey)
    {
        if ($this->isAdminUser()) {
            return;
        }

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
        if ($this->isAdminUser()) {
            return true;
        }

        return auth()->user()->can((string) $permissionKey);
    }
}

