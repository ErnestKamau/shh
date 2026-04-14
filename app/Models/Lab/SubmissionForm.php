<?php

namespace App\Models\Lab;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SubmissionForm extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name', 
        'description', 
        'naming_convention_prefix', 
        'naming_convention_format', 
        'is_published', 
        'is_active', 
        'version', 
        'created_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get the sections for this form
     */
    public function sections()
    {
        return $this->hasMany(SubmissionFormSection::class)->orderBy('sort_order');
    }
    
    /**
     * Get the instances (submissions) for this form
     */
    public function instances()
    {
        return $this->hasMany(SubmissionFormInstance::class);
    }
    
    /**
     * Get the user who created this form
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    /**
     * Get the permissions for this form
     */
    public function permissions()
    {
        return $this->hasMany(SubmissionFormPermission::class);
    }
    
    /**
     * Check if a user can access this form with a specific permission type
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
     * Check if form is published and active
     */
    public function isPublishedAndActive()
    {
        return $this->is_published && $this->is_active;
    }

    /**
     * Scope to get only published and active forms
     */
    public function scopePublishedAndActive($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    /**
     * Get the complete form structure with all nested relationships
     */
    public function getCompleteStructure()
    {
        return $this->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);
    }
}