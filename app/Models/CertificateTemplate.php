<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_published',
        'is_active',
        'version',
        'page_settings',
        'header_settings',
        'footer_settings',
        'created_by'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'page_settings' => 'array',
        'header_settings' => 'array',
        'footer_settings' => 'array'
    ];

    /**
     * Get the sections for this certificate template
     */
    public function sections()
    {
        return $this->hasMany(CertificateTemplateSection::class)->orderBy('sort_order');
    }

    /**
     * Get the root sections (sections without parent) for this template
     */
    public function rootSections()
    {
        return $this->hasMany(CertificateTemplateSection::class)
            ->whereNull('parent_section_id')
            ->orderBy('sort_order');
    }

    /**
     * Get the user who created this template
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the permissions for this certificate template
     */
    public function permissions()
    {
        return $this->hasMany(CertificateTemplatePermission::class);
    }

    /**
     * Check if a user can access this template with the specified permission type
     */
    public function canUserAccess($user, $permissionType = 'view')
    {
        // Get user's role IDs
        $userRoleIds = \App\UserRole::where('user_id', $user->id)->pluck('role_id')->toArray();
        
        return $this->permissions()
            ->where(function($query) use ($user, $userRoleIds) {
                $query->where('user_id', $user->id)
                      ->orWhereIn('role_id', $userRoleIds);
            })
            ->where('permission_type', $permissionType)
            ->exists();
    }

    /**
     * Check if the template is published and active
     */
    public function isPublishedAndActive()
    {
        return $this->is_published && $this->is_active;
    }

    /**
     * Get the total number of sections in this template
     */
    public function getSectionCount()
    {
        return $this->sections()->count();
    }

    /**
     * Get the total number of elements across all sections
     */
    public function getElementCount()
    {
        return $this->sections()
            ->with('elements')
            ->get()
            ->sum(function ($section) {
                return $section->elements->count();
            });
    }

    /**
     * Check if the template has any sections
     */
    public function hasSections()
    {
        return $this->sections()->exists();
    }

    /**
     * Get template statistics
     */
    public function getStatistics()
    {
        return [
            'total_sections' => $this->getSectionCount(),
            'total_elements' => $this->getElementCount(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_published' => $this->is_published,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Get default page settings
     */
    public function getDefaultPageSettings()
    {
        return [
            'page_size' => 'A4',
            'orientation' => 'portrait',
            'margins' => [
                'top' => '20mm',
                'right' => '15mm',
                'bottom' => '20mm',
                'left' => '15mm'
            ]
        ];
    }

    /**
     * Get page settings with defaults
     */
    public function getPageSettings()
    {
        return array_merge($this->getDefaultPageSettings(), $this->page_settings ?? []);
    }

    /**
     * Get default header settings
     */
    public function getDefaultHeaderSettings()
    {
        return [
            'enabled' => false,
            'content' => '',
            'height' => '30mm',
            'show_logo' => false,
            'logo_position' => 'left'
        ];
    }

    /**
     * Get header settings with defaults
     */
    public function getHeaderSettings()
    {
        return array_merge($this->getDefaultHeaderSettings(), $this->header_settings ?? []);
    }

    /**
     * Get default footer settings
     */
    public function getDefaultFooterSettings()
    {
        return [
            'enabled' => false,
            'content' => '',
            'height' => '20mm',
            'show_page_numbers' => false,
            'page_number_format' => 'Page {current} of {total}'
        ];
    }

    /**
     * Get footer settings with defaults
     */
    public function getFooterSettings()
    {
        return array_merge($this->getDefaultFooterSettings(), $this->footer_settings ?? []);
    }

    /**
     * Scope to filter published templates
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to filter active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter published and active templates
     */
    public function scopePublishedAndActive($query)
    {
        return $query->where('is_published', true)->where('is_active', true);
    }

    /**
     * Get the next version number for this template
     */
    public function getNextVersion()
    {
        $currentVersion = floatval($this->version);
        return number_format($currentVersion + 0.1, 1);
    }

    /**
     * Create a duplicate of this template
     */
    public function duplicate($newName = null)
    {
        $newTemplate = $this->replicate();
        $newTemplate->name = $newName ?? ($this->name . ' (Copy)');
        $newTemplate->version = '1.0';
        $newTemplate->is_published = false;
        $newTemplate->created_by = auth()->id();
        $newTemplate->save();

        // Duplicate sections and elements
        foreach ($this->rootSections as $section) {
            $this->duplicateSection($section, $newTemplate->id);
        }

        return $newTemplate;
    }

    /**
     * Recursively duplicate a section and its children
     */
    private function duplicateSection($section, $templateId, $parentSectionId = null)
    {
        $newSection = $section->replicate();
        $newSection->certificate_template_id = $templateId;
        $newSection->parent_section_id = $parentSectionId;
        $newSection->save();

        // Duplicate elements
        foreach ($section->elements as $element) {
            $newElement = $element->replicate();
            $newElement->certificate_template_section_id = $newSection->id;
            $newElement->save();
        }

        // Duplicate child sections
        foreach ($section->children as $childSection) {
            $this->duplicateSection($childSection, $templateId, $newSection->id);
        }

        return $newSection;
    }
}