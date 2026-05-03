<?php

namespace App\Policies;

use App\Models\DMS\Document;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DmsDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) ||
            $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.view');
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        if ($this->isAdmin($user) || $this->isOwner($user, $document)) {
            return true;
        }

        return $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.view');
    }

    /**
     * Determine whether the user can create documents.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin($user) ||
            $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.add');
    }

    /**
     * Determine whether the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        if ($this->isAdmin($user) || $this->isOwner($user, $document)) {
            return true;
        }

        return $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.edit');
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        return $this->isAdmin($user) ||
            $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.delete');
    }

    /**
     * Determine whether the user can amend the document.
     */
    public function amend(User $user, Document $document): bool
    {
        if ($this->isAdmin($user) || $this->isOwner($user, $document)) {
            return true;
        }

        return $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document management.edit') ||
            $this->hasPermission($user, 'documents.components.document publishing.edit');
    }

    /**
     * Determine whether the user can authorize an amendment.
     */
    public function authorizeAmendment(User $user, Document $document): bool
    {
        return $this->isAdmin($user) ||
            $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document publishing.edit');
    }

    /**
     * Determine whether the user can approve an amendment/document publication.
     */
    public function approveAmendment(User $user, Document $document): bool
    {
        return $this->isAdmin($user) ||
            $this->hasPermission($user, 'documents.permission') ||
            $this->hasPermission($user, 'documents.components.document publishing.edit');
    }

    /**
     * Determine whether the user can archive the document.
     */
    public function archive(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    /**
     * Determine whether the user can restore the document.
     */
    public function restore(User $user, Document $document): bool
    {
        return $this->update($user, $document);
    }

    private function isAdmin(User $user): bool
    {
        return $user->roles->contains(function ($role) {
            return strtolower((string) $role->name) === 'admin';
        });
    }

    private function isOwner(User $user, Document $document): bool
    {
        return (string) $document->owner_id === (string) $user->id;
    }

    private function hasPermission(User $user, string $permission): bool
    {
        $needle = strtolower($permission);

        return $user->getAllPermissions()->contains(function ($grantedPermission) use ($needle) {
            return strtolower((string) $grantedPermission->name) === $needle;
        });
    }
}