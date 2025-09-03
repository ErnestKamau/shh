<?php

namespace App\Policies;

use App\Models\CertificateTemplate;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificateTemplatePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any certificate templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        // Allow users with appropriate roles to view templates
        return $user->hasRole('admin') || 
               $user->hasRole('lab_manager') || 
               $user->hasRole('template_designer') ||
               $user->hasRole('analyst');
    }

    /**
     * Determine whether the user can view the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function view(User $user, CertificateTemplate $certificateTemplate)
    {
        // Template creator can always view
        if ($certificateTemplate->created_by === $user->id) {
            return true;
        }

        // Check if user has general view roles
        if ($user->hasRole('admin') || $user->hasRole('lab_manager')) {
            return true;
        }

        // Check template-specific permissions
        return $certificateTemplate->canUserAccess($user, 'view');
    }

    /**
     * Determine whether the user can create certificate templates.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasRole('admin') || 
               $user->hasRole('lab_manager') || 
               $user->hasRole('template_designer');
    }

    /**
     * Determine whether the user can update the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function update(User $user, CertificateTemplate $certificateTemplate)
    {
        // Template creator can always edit (unless published and they don't have publish rights)
        if ($certificateTemplate->created_by === $user->id) {
            // If template is published, need admin/manager role to edit
            if ($certificateTemplate->is_published && !($user->hasRole('admin') || $user->hasRole('lab_manager'))) {
                return false;
            }
            return true;
        }

        // Check if user has general edit roles
        if ($user->hasRole('admin') || $user->hasRole('lab_manager')) {
            return true;
        }

        // Check template-specific permissions
        return $certificateTemplate->canUserAccess($user, 'edit');
    }

    /**
     * Determine whether the user can delete the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function delete(User $user, CertificateTemplate $certificateTemplate)
    {
        // Only allow deletion if template is not published
        if ($certificateTemplate->is_published) {
            return false;
        }

        // Template creator can delete their own templates
        if ($certificateTemplate->created_by === $user->id) {
            return true;
        }

        // Check if user has general delete roles
        return $user->hasRole('admin') || $user->hasRole('lab_manager');
    }

    /**
     * Determine whether the user can publish/unpublish the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function publish(User $user, CertificateTemplate $certificateTemplate)
    {
        // Check if user has publish roles
        if ($user->hasRole('admin') || $user->hasRole('lab_manager')) {
            return true;
        }

        // Check template-specific permissions
        return $certificateTemplate->canUserAccess($user, 'publish');
    }

    /**
     * Determine whether the user can generate reports from the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function generate(User $user, CertificateTemplate $certificateTemplate)
    {
        // Template must be published and active to generate reports
        if (!$certificateTemplate->isPublishedAndActive()) {
            return false;
        }

        // Check if user has general generate roles
        if ($user->hasRole('admin') || $user->hasRole('lab_manager') || $user->hasRole('analyst')) {
            return true;
        }

        // Check template-specific permissions
        return $certificateTemplate->canUserAccess($user, 'generate');
    }

    /**
     * Determine whether the user can clone the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function clone(User $user, CertificateTemplate $certificateTemplate)
    {
        // User must be able to view the template and create new templates
        return $this->view($user, $certificateTemplate) && $this->create($user);
    }

    /**
     * Determine whether the user can export the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function export(User $user, CertificateTemplate $certificateTemplate)
    {
        // User must be able to view the template
        return $this->view($user, $certificateTemplate);
    }

    /**
     * Determine whether the user can manage permissions for the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function managePermissions(User $user, CertificateTemplate $certificateTemplate)
    {
        // Template creator can manage permissions
        if ($certificateTemplate->created_by === $user->id) {
            return true;
        }

        // Check if user has admin or manager role
        return $user->hasRole('admin') || $user->hasRole('lab_manager');
    }

    /**
     * Determine whether the user can use the template builder.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function build(User $user, CertificateTemplate $certificateTemplate)
    {
        // User must be able to update the template
        return $this->update($user, $certificateTemplate);
    }

    /**
     * Determine whether the user can preview the certificate template.
     *
     * @param  \App\User  $user
     * @param  \App\Models\CertificateTemplate  $certificateTemplate
     * @return mixed
     */
    public function preview(User $user, CertificateTemplate $certificateTemplate)
    {
        // User must be able to view the template
        return $this->view($user, $certificateTemplate);
    }
}