<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormField;
use Modules\TemplateEngine\Models\TemplateSection;
use Modules\TemplateEngine\Models\FormTemplate;

class TemplateFieldService
{
    protected $styleService;

    public function __construct()
    {
        $this->styleService = app(ContainerStyleService::class);
    }
    public function addField(TemplateSection $section, array $data): FormField
    {
        // Sanitize and prepare meta data
        $meta = $this->prepareMetaData($data['meta'] ?? [], $data['type'] ?? 'text');

        $field = $section->template->fields()->create([
            'section_id' => $section->id,
            'parent_field_id' => $data['parent_field_id'] ?? null,
            'label' => $data['label'],
            'name' => $data['name'] ?? $this->generateFieldName($data['label']),
            'type' => $data['type'],
            'required' => $data['required'] ?? false,
            'order_index' => $data['order_index'] ?? 0,
            'placeholder' => $data['placeholder'] ?? null,
            'validation_rules' => $data['validation_rules'] ?? null,
            'meta' => $meta,
        ]);

        if (isset($data['options']) && is_array($data['options'])) {
            $this->syncOptions($field, $data['options']);
        }
        
        // Handle Datast Binding if present
        if (isset($data['dataset_binding'])) {
            $field->datasetBinding()->create($data['dataset_binding']);
        }

        return $field;
    }

    public function updateField(FormField $field, array $data): FormField
    {
        // Merge and sanitize meta data
        $existingMeta = $field->meta ?? [];
        $newMeta = $data['meta'] ?? [];
        
        if ($field->type === 'container' && isset($newMeta)) {
            $data['meta'] = $this->prepareMetaData(
                array_merge($existingMeta, $newMeta),
                $field->type
            );
        } else {
            // For non-container fields, merge meta but don't sanitize CSS
            $data['meta'] = array_merge($existingMeta, $newMeta);
        }

        $field->update($data);

        if (isset($data['options']) && is_array($data['options'])) {
            $field->options()->delete(); // simplistic replace
            $this->syncOptions($field, $data['options']);
        }
        
        if (isset($data['dataset_binding'])) {
             $field->datasetBinding()->updateOrCreate([], $data['dataset_binding']);
        }

        return $field;
    }

    public function deleteField(FormField $field): bool
    {
        return $field->delete();
    }

    protected function syncOptions(FormField $field, array $options): void
    {
        foreach ($options as $index => $option) {
            $field->options()->create([
                'label' => $option['label'],
                'value' => $option['value'],
                'order_index' => $index,
                'is_default' => $option['is_default'] ?? false,
            ]);
        }
    }

    protected function generateFieldName(string $label): string
    {
        return \Illuminate\Support\Str::slug($label, '_') . '_' . uniqid();
    }

    /**
     * Prepare and sanitize meta data for container fields
     *
     * @param array $meta
     * @param string $fieldType
     * @return array
     */
    protected function prepareMetaData(array $meta, string $fieldType): array
    {
        // Handle container-specific meta
        if ($fieldType === 'container') {
            // Ensure columns is set and valid
            $meta['columns'] = isset($meta['columns']) ? max(1, min(12, (int)$meta['columns'])) : 1;

            // Sanitize CSS properties
            if (isset($meta['css']) && is_array($meta['css'])) {
                $meta['css'] = $this->sanitizeCssArray($meta['css']);
            } else {
                // Initialize empty CSS structure
                $meta['css'] = [
                    'margin_top' => '',
                    'margin_right' => '',
                    'margin_bottom' => '',
                    'margin_left' => '',
                    'padding_top' => '',
                    'padding_right' => '',
                    'padding_bottom' => '',
                    'padding_left' => '',
                    'background_color' => '',
                    'text_color' => '',
                    'border_width' => '',
                    'border_style' => '',
                    'border_color' => '',
                    'border_radius' => '',
                    'font_family' => '',
                    'font_size' => '',
                    'font_weight' => '',
                    'width' => '',
                    'height' => '',
                    'box_shadow' => '',
                    'display' => '',
                    'custom_css' => '',
                ];
            }
        }
        
        // Handle image_upload-specific meta
        if ($fieldType === 'image_upload') {
            // Ensure defaults are set
            $meta['max_size'] = isset($meta['max_size']) ? (float)$meta['max_size'] : 2;
            $meta['allowed_types'] = $meta['allowed_types'] ?? ['jpg', 'jpeg', 'png'];
            $meta['display_width'] = $meta['display_width'] ?? '200px';
            $meta['display_height'] = $meta['display_height'] ?? 'auto';
            
            // Validate and sanitize max_size
            $meta['max_size'] = max(0.1, min(20, $meta['max_size']));
            
            // Ensure allowed_types is an array
            if (!is_array($meta['allowed_types'])) {
                $meta['allowed_types'] = ['jpg', 'jpeg', 'png'];
            }
            
            // Sanitize display dimensions
            $meta['display_width'] = htmlspecialchars($meta['display_width'], ENT_QUOTES, 'UTF-8');
            $meta['display_height'] = htmlspecialchars($meta['display_height'], ENT_QUOTES, 'UTF-8');
            
            // Handle optional max dimensions
            if (isset($meta['max_width'])) {
                $meta['max_width'] = (int)$meta['max_width'];
            }
            if (isset($meta['max_height'])) {
                $meta['max_height'] = (int)$meta['max_height'];
            }
        }

        return $meta;
    }

    /**
     * Sanitize CSS array values
     *
     * @param array $css
     * @return array
     */
    protected function sanitizeCssArray(array $css): array
    {
        $sanitized = [];

        $cssProperties = [
            'margin_top', 'margin_right', 'margin_bottom', 'margin_left',
            'padding_top', 'padding_right', 'padding_bottom', 'padding_left',
            'background_color', 'text_color',
            'border_width', 'border_style', 'border_color', 'border_radius',
            'font_family', 'font_size', 'font_weight',
            'width', 'height',
            'box_shadow',
            'display',
            'custom_css',
        ];

        foreach ($cssProperties as $property) {
            if (isset($css[$property])) {
                $value = trim($css[$property]);
                
                // Map to CSS property name for validation
                $cssPropertyMap = [
                    'margin_top' => 'margin-top',
                    'margin_right' => 'margin-right',
                    'margin_bottom' => 'margin-bottom',
                    'margin_left' => 'margin-left',
                    'padding_top' => 'padding-top',
                    'padding_right' => 'padding-right',
                    'padding_bottom' => 'padding-bottom',
                    'padding_left' => 'padding-left',
                    'background_color' => 'background-color',
                    'text_color' => 'color',
                    'border_width' => 'border-width',
                    'border_style' => 'border-style',
                    'border_color' => 'border-color',
                    'border_radius' => 'border-radius',
                    'font_family' => 'font-family',
                    'font_size' => 'font-size',
                    'font_weight' => 'font-weight',
                ];

                if ($property === 'custom_css') {
                    // Extra sanitization for custom CSS
                    $sanitized[$property] = $this->styleService->sanitizeCustomCss($value);
                } else {
                    $cssProperty = $cssPropertyMap[$property] ?? $property;
                    $sanitized[$property] = $this->styleService->sanitizeCssProperty($cssProperty, $value);
                }
            } else {
                $sanitized[$property] = '';
            }
        }

        return $sanitized;
    }

    /**
     * Build inline styles from CSS array
     *
     * @param array $css
     * @return string
     */
    public function buildInlineStyles(array $css): string
    {
        return $this->styleService->buildInlineStyles($css);
    }

    /**
     * Get Bootstrap column class for given column count
     *
     * @param int $columns
     * @return string
     */
    public function getColumnClass(int $columns): string
    {
        return $this->styleService->getColumnClass($columns);
    }
}
