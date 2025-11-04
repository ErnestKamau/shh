<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplateSection extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'certificate_template_id',
        'parent_section_id',
        'title',
        'description',
        'sort_order',
        'height',
        'is_collapsible',
        'styling_options'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_collapsible' => 'boolean',
        'styling_options' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the template that owns this section.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'certificate_template_id');
    }

    /**
     * Get the parent section.
     */
    public function parentSection(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplateSection::class, 'parent_section_id');
    }

    /**
     * Get the child sections.
     */
    public function childSections(): HasMany
    {
        return $this->hasMany(CertificateTemplateSection::class, 'parent_section_id')->orderBy('sort_order');
    }

    /**
     * Get the element holders for this section.
     */
    public function elementHolders(): HasMany
    {
        return $this->hasMany(\App\Models\CertificateTemplateElementHolder::class)->orderBy('sort_order');
    }

    /**
     * Get the elements for this section.
     */
    public function elements(): HasMany
    {
        return $this->hasMany(CertificateTemplateElement::class)->orderBy('sort_order');
    }

    /**
     * Scope a query to only include root sections (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_section_id');
    }

    /**
     * Scope a query to only include child sections.
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_section_id');
    }

    /**
     * Check if this section has a parent.
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent_section_id);
    }

    /**
     * Check if this section has children.
     */
    public function hasChildren(): bool
    {
        return $this->childSections()->exists();
    }

    /**
     * Check if this section has elements.
     */
    public function hasElements(): bool
    {
        return $this->elements()->exists();
    }

    /**
     * Get the total number of elements in this section.
     */
    public function getElementsCountAttribute(): int
    {
        return $this->elements()->count();
    }

    /**
     * Get the depth level of this section (0 for root sections).
     */
    public function getDepthLevelAttribute(): int
    {
        if (!$this->hasParent()) {
            return 0;
        }

        return $this->parentSection->depth_level + 1;
    }

    /**
     * Get all ancestor sections.
     */
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $current = $this->parentSection;

        while ($current) {
            $ancestors->prepend($current);
            $current = $current->parentSection;
        }

        return $ancestors;
    }

    /**
     * Get all descendant sections.
     */
    public function getDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();
        
        foreach ($this->childSections as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }

        return $descendants;
    }
}
