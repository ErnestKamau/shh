<?php

namespace App\Services\DMS;

use App\Models\DMS\DocumentPermission;
use App\User;
use App\Models\Auth\Role;
use Illuminate\Database\Eloquent\Model;

class PermissionManager
{
    /**
     * Sync permissions for a permissionable (Document or DocumentType)
     *
     * @param Model $permissionable
     * @param array $permissionsData
     * @param int $grantedBy
     * @return void
     */
    public function syncPermissions(Model $permissionable, array $permissionsData, int $grantedBy): void
    {
        // Delete existing permissions for this permissionable
        $this->deletePermissions($permissionable);

        // Sync role-based permissions
        if (isset($permissionsData['roles']) && is_array($permissionsData['roles'])) {
            foreach ($permissionsData['roles'] as $roleId => $permissions) {
                $this->syncRolePermissions($permissionable, $roleId, $permissions, $grantedBy);
            }
        }

        // Sync user-based permissions
        if (isset($permissionsData['users']) && is_array($permissionsData['users'])) {
            foreach ($permissionsData['users'] as $userId => $permissions) {
                $this->syncUserPermissions($permissionable, $userId, $permissions, $grantedBy);
            }
        }
    }

    /**
     * Sync role permissions
     *
     * @param Model $permissionable
     * @param int $roleId
     * @param array $permissions
     * @param int $grantedBy
     * @return void
     */
    protected function syncRolePermissions(Model $permissionable, int $roleId, array $permissions, int $grantedBy): void
    {
        $permissionTypes = ['view', 'add', 'edit', 'delete', 'amend', 'authorize_amendment', 'approve_amendment'];

        foreach ($permissionTypes as $permType) {
            if (isset($permissions[$permType]) && $permissions[$permType]) {
                DocumentPermission::create([
                    'permissionable_type' => get_class($permissionable),
                    'permissionable_id' => $permissionable->id,
                    'subject_type' => Role::class,
                    'subject_id' => $roleId,
                    'permission_type' => $permType,
                    'granted_by' => $grantedBy,
                ]);
            }
        }
    }

    /**
     * Sync user permissions
     *
     * @param Model $permissionable
     * @param int $userId
     * @param array $permissions
     * @param int $grantedBy
     * @return void
     */
    protected function syncUserPermissions(Model $permissionable, int $userId, array $permissions, int $grantedBy): void
    {
        $permissionTypes = ['view', 'add', 'edit', 'delete', 'amend', 'authorize_amendment', 'approve_amendment'];

        foreach ($permissionTypes as $permType) {
            if (isset($permissions[$permType]) && $permissions[$permType]) {
                DocumentPermission::create([
                    'permissionable_type' => get_class($permissionable),
                    'permissionable_id' => $permissionable->id,
                    'subject_type' => User::class,
                    'subject_id' => $userId,
                    'permission_type' => $permType,
                    'granted_by' => $grantedBy,
                ]);
            }
        }
    }

    /**
     * Get permissions for display in UI
     *
     * @param Model $permissionable
     * @return array
     */
    public function getPermissionsForDisplay(Model $permissionable): array
    {
        $permissions = [
            'roles' => [],
            'users' => [],
        ];

        $existingPermissions = DocumentPermission::where('permissionable_type', get_class($permissionable))
            ->where('permissionable_id', $permissionable->id)
            ->get();

        foreach ($existingPermissions as $permission) {
            if ($permission->subject_type === Role::class) {
                if (!isset($permissions['roles'][$permission->subject_id])) {
                    $permissions['roles'][$permission->subject_id] = [];
                }
                $permissions['roles'][$permission->subject_id][$permission->permission_type] = true;
            } elseif ($permission->subject_type === User::class) {
                if (!isset($permissions['users'][$permission->subject_id])) {
                    $permissions['users'][$permission->subject_id] = [];
                }
                $permissions['users'][$permission->subject_id][$permission->permission_type] = true;
            }
        }

        return $permissions;
    }

    /**
     * Delete all permissions for a permissionable
     *
     * @param Model $permissionable
     * @return void
     */
    public function deletePermissions(Model $permissionable): void
    {
        DocumentPermission::where('permissionable_type', get_class($permissionable))
            ->where('permissionable_id', $permissionable->id)
            ->delete();
    }

    /**
     * Get inherited permissions from parent document type
     *
     * @param \App\Models\DMS\DocumentType $documentType
     * @return array
     */
    public function getInheritedPermissions($documentType): array
    {
        $inherited = [
            'roles' => [],
            'users' => [],
        ];

        // Walk up the parent chain
        $parent = $documentType->parent;
        while ($parent) {
            $parentPermissions = $this->getPermissionsForDisplay($parent);
            
            // Merge parent permissions (child permissions take precedence)
            foreach ($parentPermissions['roles'] as $roleId => $perms) {
                if (!isset($inherited['roles'][$roleId])) {
                    $inherited['roles'][$roleId] = $perms;
                }
            }
            
            foreach ($parentPermissions['users'] as $userId => $perms) {
                if (!isset($inherited['users'][$userId])) {
                    $inherited['users'][$userId] = $perms;
                }
            }
            
            $parent = $parent->parent;
        }

        return $inherited;
    }
}


