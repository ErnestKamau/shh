<?php

namespace App\Services\DMS;

use App\User;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentPermission;
use Illuminate\Support\Facades\Cache;

class PermissionResolver
{
    /**
     * Check if user has permission for a document
     *
     * @param User $user
     * @param Document $document
     * @param string $permissionType
     * @return bool
     */
    public function checkPermission(User $user, Document $document, string $permissionType): bool
    {
        $cacheKey = "dms_permission_{$user->id}_{$document->id}_{$permissionType}";

        return Cache::remember($cacheKey, 300, function () use ($user, $document, $permissionType) {
            return $this->resolvePermission($user, $document, $permissionType);
        });
    }

    /**
     * Check if user has permission for a document type
     *
     * @param User $user
     * @param DocumentType $documentType
     * @param string $permissionType
     * @return bool
     */
    public function checkTypePermission(User $user, DocumentType $documentType, string $permissionType): bool
    {
        $cacheKey = "dms_type_permission_{$user->id}_{$documentType->id}_{$permissionType}";

        return Cache::remember($cacheKey, 300, function () use ($user, $documentType, $permissionType) {
            return $this->resolveTypePermission($user, $documentType, $permissionType);
        });
    }

    /**
     * Resolve permission by cascading through levels
     *
     * @param User $user
     * @param Document $document
     * @param string $permissionType
     * @return bool
     */
    protected function resolvePermission(User $user, Document $document, string $permissionType): bool
    {
        // Level 1: Check document-level permissions
        if ($this->hasDirectPermission($user, $document, $permissionType)) {
            return true;
        }

        // Level 2: Check document type permissions
        if ($this->resolveTypePermission($user, $document->documentType, $permissionType)) {
            return true;
        }

        // Level 3: Check if user is the owner
        if ($document->owner_id === $user->id) {
            return in_array($permissionType, ['view', 'edit', 'amend']);
        }

        return false;
    }

    /**
     * Resolve permission for document type
     *
     * @param User $user
     * @param DocumentType $documentType
     * @param string $permissionType
     * @return bool
     */
    protected function resolveTypePermission(User $user, DocumentType $documentType, string $permissionType): bool
    {
        // Check current type
        if ($this->hasDirectPermission($user, $documentType, $permissionType)) {
            return true;
        }

        // Check parent types (inheritance)
        $parent = $documentType->parent;
        while ($parent) {
            if ($this->hasDirectPermission($user, $parent, $permissionType)) {
                return true;
            }
            $parent = $parent->parent;
        }

        return false;
    }

    /**
     * Check if user has direct permission on a permissionable
     *
     * @param User $user
     * @param mixed $permissionable
     * @param string $permissionType
     * @return bool
     */
    protected function hasDirectPermission(User $user, $permissionable, string $permissionType): bool
    {
        $userRoleIds = $user->roles->pluck('id')->toArray();

        return DocumentPermission::where('permissionable_type', get_class($permissionable))
            ->where('permissionable_id', $permissionable->id)
            ->where('permission_type', $permissionType)
            ->where(function($query) use ($user, $userRoleIds) {
                $query->where(function($q) use ($user) {
                    $q->where('subject_type', User::class)
                      ->where('subject_id', $user->id);
                })
                ->orWhere(function($q) use ($userRoleIds) {
                    $q->where('subject_type', 'App\\Models\\Role')
                      ->whereIn('subject_id', $userRoleIds);
                });
            })
            ->exists();
    }

    /**
     * Clear permission cache for a user
     *
     * @param User $user
     * @return void
     */
    public function clearUserCache(User $user): void
    {
        Cache::flush(); // In production, you'd want more targeted cache clearing
    }

    /**
     * Get all permissions for a user on a document
     *
     * @param User $user
     * @param Document $document
     * @return array
     */
    public function getUserPermissions(User $user, Document $document): array
    {
        $permissionTypes = [
            'view',
            'add',
            'edit',
            'delete',
            'amend',
            'authorize_amendment',
            'approve_amendment',
        ];

        $permissions = [];
        foreach ($permissionTypes as $type) {
            $permissions[$type] = $this->checkPermission($user, $document, $type);
        }

        return $permissions;
    }
}

