<?php

namespace App\Models;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFormPermission extends Model
{

    protected $fillable = [
        'submission_form_id',
        'role_id',
        'user_id',
        'permission_type'
    ];

    /**
     * Get the submission form that this permission belongs to
     */
    public function submissionForm(): BelongsTo
    {
        return $this->belongsTo(SubmissionForm::class);
    }

    /**
     * Get the role that this permission is assigned to
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user that this permission is assigned to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this permission is role-based
     */
    public function isRoleBased(): bool
    {
        return $this->role_id !== null;
    }

    /**
     * Check if this permission is user-specific
     */
    public function isUserSpecific(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Get the permission target (role or user)
     */
    public function getPermissionTarget()
    {
        if ($this->isUserSpecific()) {
            return $this->user;
        }
        
        if ($this->isRoleBased()) {
            return $this->role;
        }
        
        return null;
    }

    /**
     * Get the permission target name
     */
    public function getPermissionTargetName(): string
    {
        $target = $this->getPermissionTarget();
        
        if (!$target) {
            return 'Unknown';
        }
        
        if ($this->isUserSpecific()) {
            return $target->name ?? $target->email ?? 'User';
        }
        
        if ($this->isRoleBased()) {
            return $target->name ?? 'Role';
        }
        
        return 'Unknown';
    }

    /**
     * Get permission type display name
     */
    public function getPermissionTypeDisplayName(): string
    {
        switch($this->permission_type) {
            case 'view':
                return 'View';
            case 'create':
                return 'Create Instances';
            case 'edit':
                return 'Edit Template';
            case 'review':
                return 'Review Instances';
            case 'approve':
                return 'Approve Instances';
            default:
                return ucfirst($this->permission_type);
        }
    }

    /**
     * Scope to filter by permission type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('permission_type', $type);
    }

    /**
     * Scope to filter by role
     */
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter role-based permissions
     */
    public function scopeRoleBased($query)
    {
        return $query->whereNotNull('role_id');
    }

    /**
     * Scope to filter user-specific permissions
     */
    public function scopeUserSpecific($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Check if a user has this permission through role or direct assignment
     */
    public static function userHasPermission($user, int $formId, string $permissionType): bool
    {
        return static::where('submission_form_id', $formId)
            ->where('permission_type', $permissionType)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('role_id', $user->roles->pluck('id'));
            })
            ->exists();
    }

    /**
     * Get all permissions for a user on a specific form
     */
    public static function getUserPermissions($user, int $formId): array
    {
        return static::where('submission_form_id', $formId)
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('role_id', $user->roles->pluck('id'));
            })
            ->pluck('permission_type')
            ->unique()
            ->toArray();
    }

    /**
     * Grant permission to a user or role
     */
    public static function grantPermission(int $formId, string $permissionType, ?int $userId = null, ?int $roleId = null): self
    {
        return static::firstOrCreate([
            'submission_form_id' => $formId,
            'permission_type' => $permissionType,
            'user_id' => $userId,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Revoke permission from a user or role
     */
    public static function revokePermission(int $formId, string $permissionType, ?int $userId = null, ?int $roleId = null): bool
    {
        return static::where([
            'submission_form_id' => $formId,
            'permission_type' => $permissionType,
            'user_id' => $userId,
            'role_id' => $roleId,
        ])->delete() > 0;
    }
}