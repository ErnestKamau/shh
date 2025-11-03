<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplateElement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'certificate_template_section_id',
        'certificate_template_element_holder_id',
        'element_type',
        'content',
        'properties',
        'styling',
        'sort_order',
        'is_conditional',
        'conditional_logic',
        'position_x',
        'position_y',
        'width',
        'height',
        'position_x_percent',
        'position_y_percent',
        'width_percent',
        'height_percent',
        'z_index'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'properties' => 'array',
        'styling' => 'array',
        'is_conditional' => 'boolean',
        'conditional_logic' => 'array',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'position_x_percent' => 'decimal:4',
        'position_y_percent' => 'decimal:4',
        'width_percent' => 'decimal:4',
        'height_percent' => 'decimal:4',
        'z_index' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Element types constants.
     */
    const TYPE_HEADING = 'heading';
    const TYPE_PARAGRAPH = 'paragraph';
    const TYPE_TEXT = 'text';
    const TYPE_STRONG_TEXT = 'strong_text';
    const TYPE_IMAGE = 'image';
    const TYPE_DATA_FIELD = 'data_field';
    const TYPE_TABLE = 'table';
    const TYPE_SPACER = 'spacer';

    /**
     * Get all available element types.
     */
    public static function getElementTypes(): array
    {
        return [
            self::TYPE_HEADING => 'Heading',
            self::TYPE_PARAGRAPH => 'Paragraph',
            self::TYPE_TEXT => 'Text',
            self::TYPE_STRONG_TEXT => 'Strong Text',
            self::TYPE_IMAGE => 'Image',
            self::TYPE_DATA_FIELD => 'Data Field',
            self::TYPE_TABLE => 'Table',
            self::TYPE_SPACER => 'Spacer'
        ];
    }

    /**
     * Get the section that owns this element.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplateSection::class, 'certificate_template_section_id');
    }

    /**
     * Get the element holder that owns this element.
     */
    public function elementHolder(): BelongsTo
    {
        return $this->belongsTo(\App\Models\CertificateTemplateElementHolder::class, 'certificate_template_element_holder_id');
    }

    /**
     * Get the template that owns this element through the section.
     */
    public function template()
    {
        return $this->section->template();
    }

    /**
     * Scope a query to only include elements of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('element_type', $type);
    }

    /**
     * Scope a query to only include conditional elements.
     */
    public function scopeConditional($query)
    {
        return $query->where('is_conditional', true);
    }

    /**
     * Scope a query to only include non-conditional elements.
     */
    public function scopeNonConditional($query)
    {
        return $query->where('is_conditional', false);
    }

    /**
     * Check if this element is a heading.
     */
    public function isHeading(): bool
    {
        return $this->element_type === self::TYPE_HEADING;
    }

    /**
     * Check if this element is a paragraph.
     */
    public function isParagraph(): bool
    {
        return $this->element_type === self::TYPE_PARAGRAPH;
    }

    /**
     * Check if this element is an image.
     */
    public function isImage(): bool
    {
        return $this->element_type === self::TYPE_IMAGE;
    }

    /**
     * Check if this element is a data field.
     */
    public function isDataField(): bool
    {
        return $this->element_type === self::TYPE_DATA_FIELD;
    }

    /**
     * Check if this element is a table.
     */
    public function isTable(): bool
    {
        return $this->element_type === self::TYPE_TABLE;
    }

    /**
     * Check if this element is a spacer.
     */
    public function isSpacer(): bool
    {
        return $this->element_type === self::TYPE_SPACER;
    }

    /**
     * Check if this element has content.
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Check if this element has properties.
     */
    public function hasProperties(): bool
    {
        return !empty($this->properties);
    }

    /**
     * Check if this element has styling.
     */
    public function hasStyling(): bool
    {
        return !empty($this->styling);
    }

    /**
     * Get the element type label.
     */
    public function getElementTypeLabelAttribute(): string
    {
        $types = self::getElementTypes();
        return $types[$this->element_type] ?? $this->element_type;
    }

    /**
     * Get the default properties for this element type.
     */
    public function getDefaultProperties(): array
    {
        switch ($this->element_type) {
            case self::TYPE_HEADING:
                return [
                    'level' => 1,
                    'alignment' => 'left',
                    'color' => '#000000',
                    'font_size' => '24px',
                    'font_weight' => 'bold'
                ];
            case self::TYPE_PARAGRAPH:
                return [
                    'alignment' => 'left',
                    'color' => '#000000',
                    'font_size' => '14px',
                    'line_height' => '1.5'
                ];
            case self::TYPE_IMAGE:
                return [
                    'width' => '100%',
                    'height' => 'auto',
                    'alignment' => 'center',
                    'alt_text' => ''
                ];
            case self::TYPE_DATA_FIELD:
                return [
                    'field_name' => '',
                    'field_type' => 'text',
                    'format' => '',
                    'fallback' => ''
                ];
            case self::TYPE_TABLE:
                return [
                    'columns' => 2,
                    'rows' => 2,
                    'border' => true,
                    'stripe' => false,
                    'header' => true
                ];
            case self::TYPE_SPACER:
                return [
                    'height' => '20px',
                    'type' => 'divider'
                ];
            default:
                return [];
        }
    }

    /**
     * Get the merged properties with defaults.
     */
    public function getMergedPropertiesAttribute(): array
    {
        $defaults = $this->getDefaultProperties();
        return array_merge($defaults, $this->properties ?? []);
    }
}
