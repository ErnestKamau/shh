<?php

namespace App\Policies;

use App\Models\SubmissionForm;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionFormPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user is a sample reception user via Spatie roles.
     */
    private function isSampleReception(User $user): bool
    {
        return $user->roles->contains(function ($role) {
            return strtolower((string) $role->name) === 'sample reception';
        });
    }

    /**
     * Determine whether the user can view any submission forms.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('laboratory.components.rft form.view') ||
               $user->hasPermissionTo('laboratory.components.all samples.view') ||
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the submission form.
     */
    public function view(User $user, SubmissionForm $submissionForm): bool
    {
        if ($user->hasRole('admin') || $this->isSampleReception($user)) {
            return true;
        }

        if (
            $user->hasPermissionTo('laboratory.components.rft form.view') ||
            $user->hasPermissionTo('laboratory.components.all samples.view')
        ) {
            return true;
        }

        return (int) $submissionForm->created_by === (int) $user->id;
    }

    /**
     * Determine whether the user can create submission forms.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('laboratory.components.rft form.add') || 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the submission form.
     */
    public function update(User $user, SubmissionForm $submissionForm): bool
    {
        if ($user->hasRole('admin') || $this->isSampleReception($user)) {
            return true;
        }

        if (
            $user->hasPermissionTo('laboratory.components.rft form.edit') ||
            $user->hasPermissionTo('laboratory.components.all samples.edit')
        ) {
            return true;
        }

        return (int) $submissionForm->created_by === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the submission form.
     */
    public function delete(User $user, SubmissionForm $submissionForm): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasPermissionTo('laboratory.components.rft form.delete')) {
            return true;
        }

        if ((int) $submissionForm->created_by === (int) $user->id) {
            return !$submissionForm->instances()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can publish/unpublish the submission form.
     */
    public function publish(User $user, SubmissionForm $submissionForm): bool
    {
        return $this->update($user, $submissionForm);
    }

    /**
     * Determine whether the user can clone the submission form.
     */
    public function clone(User $user, SubmissionForm $submissionForm): bool
    {
        // User must be able to view the form and create new forms
        return $this->view($user, $submissionForm) && $this->create($user);
    }

    /**
     * Determine whether the user can manage permissions for the submission form.
     */
    public function managePermissions(User $user, SubmissionForm $submissionForm): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return (int) $submissionForm->created_by === (int) $user->id;
    }

    /**
     * Determine whether the user can export the submission form.
     */
    public function export(User $user, SubmissionForm $submissionForm): bool
    {
        return $this->view($user, $submissionForm);
    }
}