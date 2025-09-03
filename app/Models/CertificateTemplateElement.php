<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CertificateTemplateElement extends Model
{
    protected $fillable = [
        'certificate_template_section_id',
        'element_type',
        'content',
        'properties',
        'styling',
        'sort_order',
        'is_conditional',
        'conditional_logic'
    ];

    protected $casts = [
        'properties' => 'array',
        'styling' => 'array',
        'is_conditional' => 'boolean',
        'conditional_logic' => 'array'
    ];

    /**
     * Available element types
     */
    const ELEMENT_TYPES = [
        'heading' => 'Heading',
        'paragraph' => 'Paragraph (Rich Text)',
        'text' => 'Plain Text',
        'strong_text' => 'Strong Text',
        'image' => 'Image',
        'data_field' => 'Data Field',
        'table' => 'Table',
        'spacer' => 'Spacer'
    ];

    /**
     * Get the section that owns this element
     */
    public function section()
    {
        return $this->belongsTo(CertificateTemplateSection::class, 'certificate_template_section_id');
    }

    /**
     * Check if this element type supports rich text content
     */
    public function supportsRichText()
    {
        return in_array($this->element_type, ['paragraph']);
    }

    /**
     * Check if this element type supports plain text content
     */
    public function supportsPlainText()
    {
        return in_array($this->element_type, ['heading', 'text', 'strong_text']);
    }

    /**
     * Check if this element type supports properties configuration
     */
    public function supportsProperties()
    {
        return !in_array($this->element_type, ['spacer']);
    }

    /**
     * Check if this element type supports styling configuration
     */
    public function supportsStyling()
    {
        return true; // All elements support some form of styling
    }

    /**
     * Check if this element type supports conditional logic
     */
    public function supportsConditionalLogic()
    {
        return !in_array($this->element_type, ['spacer']);
    }

    /**
     * Get default properties for this element type
     */
    public function getDefaultProperties()
    {
        switch ($this->element_type) {
            case 'heading':
                return [
                    'level' => 'h2',
                    'text' => 'Heading Text',
                    'alignment' => 'left'
                ];
            
            case 'paragraph':
                return [
                    'editor_type' => 'tinymce'
                ];
            
            case 'text':
                return [
                    'text' => 'Plain text content',
                    'alignment' => 'left'
                ];
            
            case 'strong_text':
                return [
                    'text' => 'Strong text content',
                    'alignment' => 'left'
                ];
            
            case 'image':
                return [
                    'image_path' => '',
                    'alt_text' => '',
                    'width' => 'auto',
                    'height' => 'auto',
                    'alignment' => 'center'
                ];
            
            case 'data_field':
                return [
                    'field_source' => 'submission_form',
                    'field_name' => '',
                    'display_label' => '',
                    'default_value' => 'N/A',
                    'format_type' => 'text'
                ];
            
            case 'table':
                return [
                    'headers' => [],
                    'data_source' => 'submission_form_elements',
                    'filter_criteria' => [],
                    'show_borders' => true,
                    'alternate_rows' => false
                ];
            
            case 'spacer':
                return [
                    'height' => '20px'
                ];
            
            default:
                return [];
        }
    }

    /**
     * Get properties with defaults
     */
    public function getProperties()
    {
        return array_merge($this->getDefaultProperties(), $this->properties ?? []);
    }

    /**
     * Get default styling for this element type
     */
    public function getDefaultStyling()
    {
        switch ($this->element_type) {
            case 'heading':
                return [
                    'font_size' => '18px',
                    'font_weight' => 'bold',
                    'color' => '#000000',
                    'margin_bottom' => '10px',
                    'text_align' => 'left'
                ];
            
            case 'paragraph':
                return [
                    'line_height' => '1.5',
                    'text_align' => 'justify',
                    'margin_bottom' => '15px'
                ];
            
            case 'text':
            case 'strong_text':
                return [
                    'font_size' => '12px',
                    'color' => '#000000',
                    'margin_bottom' => '10px',
                    'text_align' => 'left'
                ];
            
            case 'image':
                return [
                    'margin_bottom' => '15px',
                    'display' => 'block'
                ];
            
            case 'data_field':
                return [
                    'font_size' => '12px',
                    'color' => '#000000',
                    'margin_bottom' => '5px'
                ];
            
            case 'table':
                return [
                    'border_collapse' => 'collapse',
                    'width' => '100%',
                    'margin_bottom' => '20px'
                ];
            
            case 'spacer':
                return [
                    'display' => 'block',
                    'width' => '100%'
                ];
            
            default:
                return [];
        }
    }

    /**
     * Get styling with defaults
     */
    public function getStyling()
    {
        return array_merge($this->getDefaultStyling(), $this->styling ?? []);
    }

    /**
     * Check if this element should be visible based on conditional logic
     */
    public function shouldBeVisible($formData = [])
    {
        if (!$this->is_conditional || empty($this->conditional_logic)) {
            return true;
        }

        // Simple conditional logic implementation
        foreach ($this->conditional_logic as $condition) {
            if (!isset($condition['field'], $condition['operator'], $condition['value'])) {
                continue;
            }

            $fieldValue = $formData[$condition['field']] ?? null;
            
            switch ($condition['operator']) {
                case 'equals':
                    if ($fieldValue != $condition['value']) {
                        return false;
                    }
                    break;
                case 'not_equals':
                    if ($fieldValue == $condition['value']) {
                        return false;
                    }
                    break;
                case 'contains':
                    if (strpos($fieldValue, $condition['value']) === false) {
                        return false;
                    }
                    break;
                case 'not_contains':
                    if (strpos($fieldValue, $condition['value']) !== false) {
                        return false;
                    }
                    break;
                case 'empty':
                    if (!empty($fieldValue)) {
                        return false;
                    }
                    break;
                case 'not_empty':
                    if (empty($fieldValue)) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Get the rendered content for this element
     */
    public function getRenderedContent($formData = [])
    {
        if (!$this->shouldBeVisible($formData)) {
            return '';
        }

        $content = $this->content ?? '';
        $properties = $this->getProperties();

        switch ($this->element_type) {
            case 'heading':
                $level = $properties['level'] ?? 'h2';
                $text = $properties['text'] ?? 'Heading';
                return "<{$level}>{$text}</{$level}>";
            
            case 'paragraph':
                return $content; // Rich HTML content
            
            case 'text':
                $text = $properties['text'] ?? '';
                return "<p>{$text}</p>";
            
            case 'strong_text':
                $text = $properties['text'] ?? '';
                return "<p><strong>{$text}</strong></p>";
            
            case 'image':
                $imagePath = $properties['image_path'] ?? '';
                $altText = $properties['alt_text'] ?? '';
                $width = $properties['width'] ?? 'auto';
                $height = $properties['height'] ?? 'auto';
                
                if (empty($imagePath)) {
                    return '<div class="image-placeholder">Image not configured</div>';
                }
                
                return "<img src=\"{$imagePath}\" alt=\"{$altText}\" style=\"width: {$width}; height: {$height};\">";
            
            case 'data_field':
                $fieldName = $properties['field_name'] ?? '';
                $displayLabel = $properties['display_label'] ?? '';
                $defaultValue = $properties['default_value'] ?? 'N/A';
                $fieldValue = $formData[$fieldName] ?? $defaultValue;
                
                $label = $displayLabel ? "{$displayLabel} " : '';
                return "<span class=\"data-field\">{$label}{$fieldValue}</span>";
            
            case 'spacer':
                $height = $properties['height'] ?? '20px';
                return "<div style=\"height: {$height};\"></div>";
            
            case 'table':
                // Table rendering would be more complex and handled separately
                return '<div class="table-placeholder">Table content</div>';
            
            default:
                return $content;
        }
    }

    /**
     * Scope to order elements by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope to filter by element type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('element_type', $type);
    }

    /**
     * Scope to filter conditional elements
     */
    public function scopeConditional($query)
    {
        return $query->where('is_conditional', true);
    }

    /**
     * Get the next sort order for a new element in the same section
     */
    public static function getNextSortOrder($sectionId)
    {
        $maxSortOrder = static::where('certificate_template_section_id', $sectionId)
                             ->max('sort_order');
        
        return ($maxSortOrder ?? 0) + 1;
    }

    /**
     * Validate element properties based on type
     */
    public function validateProperties()
    {
        $properties = $this->getProperties();
        $errors = [];

        switch ($this->element_type) {
            case 'heading':
                if (empty($properties['text'])) {
                    $errors[] = 'Heading text is required';
                }
                if (!in_array($properties['level'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                    $errors[] = 'Invalid heading level';
                }
                break;
            
            case 'data_field':
                if (empty($properties['field_name'])) {
                    $errors[] = 'Field name is required for data field elements';
                }
                break;
            
            case 'image':
                if (empty($properties['image_path'])) {
                    $errors[] = 'Image path is required for image elements';
                }
                break;
        }

        return $errors;
    }
}