<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormField;
use Modules\TemplateEngine\Models\TemplateSection;
use Modules\TemplateEngine\Models\FormTemplate;

class TemplateFieldService
{
    public function addField(TemplateSection $section, array $data): FormField
    {
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
            'meta' => $data['meta'] ?? null,
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
}
