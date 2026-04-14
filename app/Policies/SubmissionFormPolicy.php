<?php

namespace App\Policies;

use App\Models\SubmissionForm;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubmissionFormPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any submission forms.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Laboratory.components.RFT Form.View') ||
               $user->hasPermissionTo('Laboratory.components.All Samples.View') ||
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can view the submission form.
     */
    public function view(User $user, SubmissionForm $submissionForm): bool
    {
        // Admin can view all forms
        if ($user->hasRole('admin')) {
            return true;
        }

        // Check if user has specific permission for this form
        return $submissionForm->canUserAccess($user, 'view');
    }

    /**
     * Determine whether the user can create submission forms.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Laboratory.components.RFT Form.Add') || 
               $user->hasRole('admin');
    }

    /**
     * Determine whether the user can update the submission form.
     */
    public function update(User $user, SubmissionForm $submissionForm): bool
    {
        // Admin can update all forms
        if ($user->hasRole('admin')) {
            return true;
        }

        // Form creator can always update their forms
        if ($submissionForm->created_by === $user->id) {
            return true;
        }

        // Check if user has edit permission for this form
        return $submissionForm->canUserAccess($user, 'edit');
    }

    /**
     * Determine whether the user can delete the submission form.
     */
    public function delete(User $user, SubmissionForm $submissionForm): bool
    {
        // Admin can delete all forms
        if ($user->hasRole('admin')) {
            return true;
        }

        // Form creator can delete their forms if no instances exist
        if ($submissionForm->created_by === $user->id) {
            return !$submissionForm->instances()->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can publish/unpublish the submission form.
     */
    public function publish(User $user, SubmissionForm $submissionForm): bool
    {
        // Admin can publish/unpublish all forms
        if ($user->hasRole('admin')) {
            return true;
        }

        // Form creator can publish their forms
        if ($submissionForm->created_by === $user->id) {
            return true;
        }

        // Check if user has edit permission for this form
        return $submissionForm->canUserAccess($user, 'edit');
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
        // Admin can manage permissions for all forms
        if ($user->hasRole('admin')) {
            return true;
        }

        // Form creator can manage permissions for their forms
        return $submissionForm->created_by === $user->id;
    }

    /**
     * Determine whether the user can export the submission form.
     */
    public function export(User $user, SubmissionForm $submissionForm): bool
    {
        return $this->view($user, $submissionForm);
    }
}