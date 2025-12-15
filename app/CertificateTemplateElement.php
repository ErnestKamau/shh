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
        'z_index',
        'css_config',
        'data_config',
        'parent_cell_id'
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
        'css_config' => 'array',
        'data_config' => 'array',
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
    const TYPE_BUTTON = 'button';
    const TYPE_DIVIDER = 'divider';
    const TYPE_LIST = 'list';
    const TYPE_ICON = 'icon';
    const TYPE_CUSTOM_HTML = 'custom_html';
    const TYPE_INPUT_TEXT = 'input_text';
    const TYPE_INPUT_EMAIL = 'input_email';
    const TYPE_INPUT_NUMBER = 'input_number';
    const TYPE_INPUT_DATE = 'input_date';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_SELECT = 'select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_LINK = 'link';
    const TYPE_BLOCKQUOTE = 'blockquote';
    const TYPE_CODE_BLOCK = 'code_block';

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
            self::TYPE_SPACER => 'Spacer',
            self::TYPE_BUTTON => 'Button',
            self::TYPE_DIVIDER => 'Divider',
            self::TYPE_LIST => 'List',
            self::TYPE_ICON => 'Icon',
            self::TYPE_CUSTOM_HTML => 'Custom HTML',
            self::TYPE_INPUT_TEXT => 'Text Input',
            self::TYPE_INPUT_EMAIL => 'Email Input',
            self::TYPE_INPUT_NUMBER => 'Number Input',
            self::TYPE_INPUT_DATE => 'Date Input',
            self::TYPE_TEXTAREA => 'Textarea',
            self::TYPE_SELECT => 'Select Dropdown',
            self::TYPE_CHECKBOX => 'Checkbox',
            self::TYPE_RADIO => 'Radio Button',
            self::TYPE_LINK => 'Link',
            self::TYPE_BLOCKQUOTE => 'Blockquote',
            self::TYPE_CODE_BLOCK => 'Code Block'
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
            case self::TYPE_CHECKBOX:
                return [
                    'label' => 'Checkbox Label',
                    'checked' => false
                ];
            case self::TYPE_RADIO:
                return [
                    'label' => 'Radio Option',
                    'checked' => false,
                    'group' => 'default_group'
                ];
            case self::TYPE_LINK:
                return [
                    'url' => '#',
                    'target' => '_blank',
                    'color' => '#4f46e5'
                ];
            case self::TYPE_BLOCKQUOTE:
                return [
                    'border_left_color' => '#e5e7eb',
                    'font_style' => 'italic'
                ];
            case self::TYPE_CODE_BLOCK:
                return [
                    'language' => 'text',
                    'theme' => 'light'
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
