<?php

namespace App\Models;

use App\User;
use App\CertificateTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionForm extends Model
{
    protected $fillable = [
        'name',
        'description',
        'naming_convention_prefix',
        'naming_convention_format',
        'is_published',
        'is_active',
        'start_submission_number',
        'version',
        'print_template_name',
        'created_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get the sections for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sections()
    {
        return $this->hasMany(SubmissionFormSection::class)->orderBy('sort_order');
    }

    /**
     * Get the instances for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function instances()
    {
        return $this->hasMany(SubmissionFormInstance::class);
    }

    /**
     * Get the user who created this form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the permissions for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(SubmissionFormPermission::class);
    }

    /**
     * Get the certificate templates for this submission form
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function certificateTemplates()
    {
        return $this->hasMany(CertificateTemplate::class);
    }

    /**
     * Check if a user can access this form with the specified permission type
     */
    public function canUserAccess($user, $permissionType = 'view')
    {
        return $this->permissions()
            ->where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('role_id', $user->roles->pluck('id'));
            })
            ->where('permission_type', $permissionType)
            ->exists();
    }

    /**
     * Check if the form is published and active
     * 
     * @return bool
     */
    public function isPublishedAndActive()
    {
        return $this->is_published && $this->is_active;
    }

    /**
     * Get the total number of sections in this form
     * 
     * @return int
     */
    public function getSectionCount()
    {
        return $this->sections()->count();
    }

    /**
     * Get the total number of elements across all sections
     * 
     * @return int
     */
    public function getElementCount()
    {
        return $this->sections()
            ->with('elementHolders.elements')
            ->get()
            ->sum(function ($section) {
                return $section->elementHolders->sum(function ($holder) {
                    return $holder->elements->count();
                });
            });
    }

    /**
     * Check if the form has any sections
     * 
     * @return bool
     */
    public function hasSections()
    {
        return $this->sections()->exists();
    }

    /**
     * Get form statistics
     * 
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_sections' => $this->getSectionCount(),
            'total_elements' => $this->getElementCount(),
            'total_instances' => $this->instances()->count(),
            'draft_instances' => $this->instances()->where('status', 'draft')->count(),
            'submitted_instances' => $this->instances()->where('status', 'submitted')->count(),
            'approved_instances' => $this->instances()->where('status', 'approved')->count(),
        ];
    }

    /**
     * Get the print template name for this form
     * 
     * @return string
     */
    public function getPrintTemplateName(): string
    {
        return $this->print_template_name ?? 'submission-forms.print.default';
    }
}