<?php

namespace App\Policies;

use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class SubmissionFormInstancePolicy
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
     * Determine whether the user can view any submission form instances.
     */
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('viewAny', \App\Models\SubmissionForm::class);
    }

    /**
     * Determine whether the user can view the submission form instance.
     */
    public function view(User $user, SubmissionFormInstance $instance): bool
    {
        if ($this->update($user, $instance)) {
            return true;
        }

        if ($this->viewAny($user)) {
            return true;
        }

        $submissionForm = $instance->submissionForm;

        return $submissionForm !== null
            && Gate::forUser($user)->allows('view', $submissionForm);
    }

    /**
     * Determine whether the user can update the submission form instance.
     */
    public function update(User $user, SubmissionFormInstance $instance): bool
    {
        if ($this->isSampleReception($user)) {
            return true;
        }

        if ((int) $instance->submitted_by === (int) $user->id) {
            return true;
        }

        $submissionForm = $instance->submissionForm;

        return $submissionForm !== null
            && Gate::forUser($user)->allows('update', $submissionForm);
    }
}