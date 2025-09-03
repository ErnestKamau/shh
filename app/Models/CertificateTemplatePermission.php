<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplatePermission extends Model
{
    protected $fillable = [
        'certificate_template_id',
        'user_id',
        'role_id',
        'permission_type'
    ];

    /**
     * Available permission types
     */
    const PERMISSION_TYPES = [
        'view' => 'View Template',
        'edit' => 'Edit Template',
        'publish' => 'Publish Template',
        'generate' => 'Generate Reports'
    ];

    /**
     * Get the certificate template that owns this permission
     */
    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    /**
     * Get the user this permission belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role this permission belongs to (if applicable)
     */
    public function role()
    {
        return $this->belongsTo(\App\Role::class);
    }

    /**
     * Check if this permission is for a user (not role-based)
     */
    public function isUserPermission()
    {
        return !is_null($this->user_id);
    }

    /**
     * Check if this permission is role-based (not user-specific)
     */
    public function isRolePermission()
    {
        return !is_null($this->role_id);
    }

    /**
     * Get the permission level as a numeric value for comparison
     */
    public function getPermissionLevel()
    {
        $levels = [
            'view' => 1,
            'edit' => 2,
            'publish' => 3,
            'generate' => 4
        ];

        return $levels[$this->permission_type] ?? 0;
    }

    /**
     * Check if this permission includes the specified permission type
     */
    public function includes($permissionType)
    {
        $hierarchy = [
            'view' => ['view'],
            'edit' => ['view', 'edit'],
            'publish' => ['view', 'edit', 'publish'],
            'generate' => ['view', 'generate']
        ];

        $allowedPermissions = $hierarchy[$this->permission_type] ?? [];
        
        return in_array($permissionType, $allowedPermissions);
    }

    /**
     * Scope to filter by permission type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('permission_type', $type);
    }

    /**
     * Scope to filter user permissions
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter role permissions
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope to filter by template
     */
    public function scopeForTemplate($query, $templateId)
    {
        return $query->where('certificate_template_id', $templateId);
    }

    /**
     * Create or update permission for a user
     */
    public static function setUserPermission($templateId, $userId, $permissionType)
    {
        return static::updateOrCreate(
            [
                'certificate_template_id' => $templateId,
                'user_id' => $userId,
                'role_id' => null
            ],
            [
                'permission_type' => $permissionType
            ]
        );
    }

    /**
     * Create or update permission for a role
     */
    public static function setRolePermission($templateId, $roleId, $permissionType)
    {
        return static::updateOrCreate(
            [
                'certificate_template_id' => $templateId,
                'role_id' => $roleId,
                'user_id' => null
            ],
            [
                'permission_type' => $permissionType
            ]
        );
    }

    /**
     * Remove permission for a user
     */
    public static function removeUserPermission($templateId, $userId)
    {
        return static::where('certificate_template_id', $templateId)
                    ->where('user_id', $userId)
                    ->delete();
    }

    /**
     * Remove permission for a role
     */
    public static function removeRolePermission($templateId, $roleId)
    {
        return static::where('certificate_template_id', $templateId)
                    ->where('role_id', $roleId)
                    ->delete();
    }

    /**
     * Get all permissions for a template grouped by type
     */
    public static function getTemplatePermissions($templateId)
    {
        $permissions = static::where('certificate_template_id', $templateId)
                            ->with(['user', 'role'])
                            ->get();

        return $permissions->groupBy('permission_type');
    }

    /**
     * Check if a user has specific permission for a template
     */
    public static function userHasPermission($templateId, $userId, $permissionType, $userRoleIds = [])
    {
        // Check direct user permission
        $userPermission = static::where('certificate_template_id', $templateId)
                               ->where('user_id', $userId)
                               ->first();

        if ($userPermission && $userPermission->includes($permissionType)) {
            return true;
        }

        // Check role-based permissions
        if (!empty($userRoleIds)) {
            $rolePermissions = static::where('certificate_template_id', $templateId)
                                   ->whereIn('role_id', $userRoleIds)
                                   ->get();

            foreach ($rolePermissions as $rolePermission) {
                if ($rolePermission->includes($permissionType)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the highest permission level for a user on a template
     */
    public static function getUserHighestPermission($templateId, $userId, $userRoleIds = [])
    {
        $permissions = collect();

        // Get direct user permissions
        $userPermission = static::where('certificate_template_id', $templateId)
                               ->where('user_id', $userId)
                               ->first();

        if ($userPermission) {
            $permissions->push($userPermission);
        }

        // Get role-based permissions
        if (!empty($userRoleIds)) {
            $rolePermissions = static::where('certificate_template_id', $templateId)
                                   ->whereIn('role_id', $userRoleIds)
                                   ->get();

            $permissions = $permissions->merge($rolePermissions);
        }

        if ($permissions->isEmpty()) {
            return null;
        }

        // Return the permission with the highest level
        return $permissions->sortByDesc(function ($permission) {
            return $permission->getPermissionLevel();
        })->first();
    }
}