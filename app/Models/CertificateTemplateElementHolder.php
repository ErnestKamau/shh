<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplateElementHolder extends Model
{
    protected $fillable = [
        'certificate_template_section_id',
        'holder_type',
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
     * Get the elements for the holder.
     */
    public function elements(): HasMany
    {
        return $this->hasMany(\App\CertificateTemplateElement::class, 'certificate_template_element_holder_id')->orderBy('sort_order');
    }

    /**
     * Check if the holder has capacity for more elements.
     */
    public function hasCapacity(): bool
    {
        return $this->elements()->count() < $this->max_elements;
    }

    /**
     * Get the number of available slots.
     */
    public function availableSlots(): int
    {
        return max(0, $this->max_elements - $this->elements()->count());
    }
}
