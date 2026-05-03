<?php

namespace App\Services\DMS;

use App\User;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use Illuminate\Support\Facades\Gate;

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
        return match ($permissionType) {
            'view' => Gate::forUser($user)->allows('view', $document),
            'add' => Gate::forUser($user)->allows('create', Document::class),
            'edit' => Gate::forUser($user)->allows('update', $document),
            'delete' => Gate::forUser($user)->allows('delete', $document),
            'amend' => Gate::forUser($user)->allows('amend', $document),
            'authorize_amendment' => Gate::forUser($user)->allows('authorizeAmendment', $document),
            'approve_amendment' => Gate::forUser($user)->allows('approveAmendment', $document),
            default => false,
        };
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
        return match ($permissionType) {
            'view' => $this->hasPermission($user, 'documents.components.document types.view') ||
                $this->hasPermission($user, 'documents.permission'),
            'add' => $this->hasPermission($user, 'documents.components.document types.add') ||
                $this->hasPermission($user, 'documents.permission'),
            'edit', 'amend', 'authorize_amendment', 'approve_amendment' =>
                $this->hasPermission($user, 'documents.components.document types.edit') ||
                $this->hasPermission($user, 'documents.permission'),
            'delete' => $this->hasPermission($user, 'documents.components.document types.delete') ||
                $this->hasPermission($user, 'documents.permission'),
            default => false,
        };
    }

    /**
     * Clear permission cache for a user
     *
     * @param User $user
     * @return void
     */
    public function clearUserCache(User $user): void
    {
        // No cache is used once authorization is delegated to Gate/policies.
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

    private function hasPermission(User $user, string $permission): bool
    {
        $needle = strtolower($permission);

        return $user->getAllPermissions()->contains(function ($grantedPermission) use ($needle) {
            return strtolower((string) $grantedPermission->name) === $needle;
        });
    }
}

