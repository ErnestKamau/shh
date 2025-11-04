<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplateElementHolder extends Model
{
    protected $fillable = [
        'certificate_template_section_id',
        'parent_holder_id',
        'holder_type',
        'direction',
        'data_source',
        'field_mappings',
        'max_elements',
        'sort_order',
        'position_x',
        'position_y',
        'width',
        'height',
        'position_x_percent',
        'position_y_percent',
        'width_percent',
        'height_percent',
        'flex_grow',
        'flex_shrink',
        'flex_basis',
        // Company Information (deprecated - use data_source and field_mappings instead)
        'company_name',
        'company_email',
        'company_website',
        'company_phone',
        'company_logo',
        // Document QA Details (deprecated - use data_source and field_mappings instead)
        'form_number',
        'publish_date',
        'qa_other_details',
    ];

    protected function casts(): array
    {
        return [
            'max_elements' => 'integer',
            'sort_order' => 'integer',
            'position_x' => 'decimal:2',
            'position_y' => 'decimal:2',
            'width' => 'decimal:2',
            'height' => 'decimal:2',
            'position_x_percent' => 'decimal:4',
            'position_y_percent' => 'decimal:4',
            'width_percent' => 'decimal:4',
            'height_percent' => 'decimal:4',
            'field_mappings' => 'array',
            'flex_grow' => 'decimal:2',
            'flex_shrink' => 'decimal:2',
            'publish_date' => 'date',
        ];
    }

    /**
     * Get the section that owns the element holder.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(\App\CertificateTemplateSection::class, 'certificate_template_section_id');
    }

    /**
     * Get the parent holder (for nesting).
     */
    public function parentHolder(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_holder_id');
    }

    /**
     * Get child holders (nested holders).
     */
    public function childHolders(): HasMany
    {
        return $this->hasMany(self::class, 'parent_holder_id')->orderBy('sort_order');
    }

    /**
     * Get all holders recursively (children and nested children).
     */
    public function getAllChildHolders()
    {
        return $this->childHolders()->with('allChildHolders')->get();
    }

    /**
     * Get the elements for the holder.
     */
    public function elements(): HasMany
    {
        return $this->hasMany(\App\CertificateTemplateElement::class, 'certificate_template_element_holder_id')->orderBy('sort_order');
    }

    /**
     * Get all children (both holders and elements).
     */
    public function getChildren()
    {
        return [
            'holders' => $this->childHolders,
            'elements' => $this->elements,
        ];
    }

    /**
     * Check if the holder has capacity for more elements.
     */
    public function hasCapacity(): bool
    {
        if ($this->max_elements === null || $this->max_elements === 0) {
            return true; // Unlimited capacity
        }
        return ($this->elements()->count() + $this->childHolders()->count()) < $this->max_elements;
    }

    /**
     * Get the number of available slots.
     */
    public function availableSlots(): int
    {
        if ($this->max_elements === null || $this->max_elements === 0) {
            return 999; // Unlimited
        }
        return max(0, $this->max_elements - ($this->elements()->count() + $this->childHolders()->count()));
    }

    /**
     * Check if holder is nested (has a parent).
     */
    public function isNested(): bool
    {
        return $this->parent_holder_id !== null;
    }

    /**
     * Get nesting depth.
     */
    public function getDepth(): int
    {
        if (!$this->parent_holder_id) {
            return 0;
        }
        return $this->parentHolder ? $this->parentHolder->getDepth() + 1 : 1;
    }
}
