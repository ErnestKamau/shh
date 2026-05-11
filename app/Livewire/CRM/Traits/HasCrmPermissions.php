<?php

namespace App\Livewire\CRM\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

trait HasCrmPermissions
{
    protected function isAdminUser(): bool
    {
        /** @var \App\User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return true;
        }

        return $user->hasRole('admin');
    }

    protected function canAccessPermission($user, string $permissionKey): bool
    {
        /** @var \App\User $user */
        try {
            if ($user->can($permissionKey)) {
                return true;
            }
        } catch (\Throwable $exception) {
            Log::warning('Primary CRM permission check failed.', [
                'user_id' => $user->id ?? null,
                'permission' => $permissionKey,
                'error' => $exception->getMessage(),
            ]);
        }

        try {
            foreach ($user->roles as $role) {
                if ($role->hasPermissionTo($permissionKey)) {
                    return true;
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('Fallback CRM role permission check failed.', [
                'user_id' => $user->id ?? null,
                'permission' => $permissionKey,
                'error' => $exception->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Check if user has permission, abort if not
      * @param string $permissionKey Format: "crm.resource.action"
     */
    protected function checkPermission($permissionKey)
    {
        /** @var \App\User|null $user */
        $user = Auth::user();

        if (! $user) {
            abort(403, 'You do not have permission to perform this action.');
        }

        if ($this->isAdminUser()) {
            return;
        }

        if (! $this->canAccessPermission($user, (string) $permissionKey)) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $user->unsetRelation('roles');
            $user->load('roles.permissions');
        }

        if (! $this->canAccessPermission($user, (string) $permissionKey)) {
            abort(403, 'You do not have permission to perform this action.');
        }
    }

    /**
     * Check if user has permission, return boolean
      * @param string $permissionKey Format: "crm.resource.action"
     * @return bool
     */
    protected function hasPermission($permissionKey)
    {
        /** @var \App\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($this->isAdminUser()) {
            return true;
        }

        if ($this->canAccessPermission($user, (string) $permissionKey)) {
            return true;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $user->unsetRelation('roles');
        $user->load('roles.permissions');

        return $this->canAccessPermission($user, (string) $permissionKey);
    }
}

