<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplateSection extends Model
{
    protected $fillable = [
        'certificate_template_id',
        'parent_section_id',
        'title',
        'description',
        'sort_order',
        'is_collapsible',
        'styling_options'
    ];

    protected $casts = [
        'is_collapsible' => 'boolean',
        'styling_options' => 'array'
    ];

    /**
     * Get the certificate template that owns this section
     */
    public function certificateTemplate()
    {
        return $this->belongsTo(CertificateTemplate::class);
    }

    /**
     * Get the parent section (for subsections)
     */
    public function parent()
    {
        return $this->belongsTo(CertificateTemplateSection::class, 'parent_section_id');
    }

    /**
     * Get the child sections (subsections)
     */
    public function children()
    {
        return $this->hasMany(CertificateTemplateSection::class, 'parent_section_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get all elements in this section
     */
    public function elements()
    {
        return $this->hasMany(CertificateTemplateElement::class)
                    ->orderBy('sort_order');
    }

    /**
     * Get the total number of child sections
     */
    public function getChildSectionCount()
    {
        return $this->children()->count();
    }

    /**
     * Get the total number of elements in this section
     */
    public function getElementCount()
    {
        return $this->elements()->count();
    }

    /**
     * Check if this section has any child sections
     */
    public function hasChildren()
    {
        return $this->children()->exists();
    }

    /**
     * Check if this section has any elements
     */
    public function hasElements()
    {
        return $this->elements()->exists();
    }

    /**
     * Get the nesting level of this section (0 = root, 1 = first level, etc.)
     */
    public function getNestingLevel()
    {
        $level = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        
        return $level;
    }

    /**
     * Check if this section can have child sections (max 3 levels)
     */
    public function canHaveChildren()
    {
        return $this->getNestingLevel() < 2; // 0, 1, 2 are allowed (3 levels total)
    }

    /**
     * Get all ancestor sections
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        
        while ($parent) {
            $ancestors->prepend($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    /**
     * Get all descendant sections (children, grandchildren, etc.)
     */
    public function getDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }
        
        return $descendants;
    }

    /**
     * Check if this section is an ancestor of the given section
     */
    public function isAncestorOf(CertificateTemplateSection $section)
    {
        return $section->getAncestors()->contains('id', $this->id);
    }

    /**
     * Check if this section is a descendant of the given section
     */
    public function isDescendantOf(CertificateTemplateSection $section)
    {
        return $this->getAncestors()->contains('id', $section->id);
    }

    /**
     * Scope to order sections by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to get root sections (no parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_section_id');
    }

    /**
     * Scope to get sections by nesting level
     */
    public function scopeByLevel($query, $level)
    {
        if ($level === 0) {
            return $query->whereNull('parent_section_id');
        }
        
        // This is a simplified approach - for complex queries, 
        // you might want to use a recursive CTE or adjacency list pattern
        return $query->whereNotNull('parent_section_id');
    }

    /**
     * Get the next sort order for a new section in the same parent
     */
    public static function getNextSortOrder($templateId, $parentSectionId = null)
    {
        $query = static::where('certificate_template_id', $templateId);
        
        if ($parentSectionId) {
            $query->where('parent_section_id', $parentSectionId);
        } else {
            $query->whereNull('parent_section_id');
        }
        
        $maxSortOrder = $query->max('sort_order');
        
        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Get default styling options
     */
    public function getDefaultStylingOptions()
    {
        return [
            'background_color' => 'transparent',
            'border_width' => '0px',
            'border_color' => '#000000',
            'border_style' => 'solid',
            'padding' => '10px',
            'margin_bottom' => '20px'
        ];
    }

    /**
     * Get styling options with defaults
     */
    public function getStylingOptions()
    {
        return array_merge($this->getDefaultStylingOptions(), $this->styling_options ?? []);
    }

    /**
     * Move this section to a new parent (with validation)
     */
    public function moveTo($newParentId = null)
    {
        // Validate that we're not creating a circular reference
        if ($newParentId) {
            $newParent = static::find($newParentId);
            if (!$newParent || $newParent->isDescendantOf($this)) {
                throw new \InvalidArgumentException('Cannot move section: would create circular reference');
            }
            
            // Check nesting level
            if ($newParent->getNestingLevel() >= 2) {
                throw new \InvalidArgumentException('Cannot move section: maximum nesting level exceeded');
            }
        }
        
        $this->parent_section_id = $newParentId;
        $this->sort_order = static::getNextSortOrder($this->certificate_template_id, $newParentId);
        $this->save();
        
        return $this;
    }
}