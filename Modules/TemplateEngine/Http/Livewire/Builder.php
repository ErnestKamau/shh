<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Models\TemplateSection;
use Modules\TemplateEngine\Services\TemplateCreationService;
use Modules\TemplateEngine\Services\TemplateFieldService;
use Modules\TemplateEngine\Models\FormField;
use Modules\TemplateEngine\Services\VariableManager;
use Modules\TemplateEngine\Models\FormTemplateVariable;

class Builder extends Component
{
    use WithFileUploads;
    
    public $template;
    public $sections;
    
    // Modal state
    public $showFieldModal = false;
    public $currentSectionId;
    public $editingFieldId = null;
    
    // File upload for static images
    public $staticImageUpload;
    
    // Field Data
    public $fieldData = [
        'label' => '',
        'type' => 'text',
        'required' => false,
        'parent_field_id' => null,
        'options' => [], // for select/radio
        'dataset_binding' => null, // for dynamic
        'meta' => [],
    ];

    protected $listeners = ['refreshBuilder' => '$refresh', 'datasetSelected' => 'handleDatasetBinding'];

    // Variables State
    public $showVariablesPanel = false;
    public $showVariableModal = false;
    public $variables = [];
    public $variableData = [
        'id' => null,
        'name' => '',
        'type' => 'static', // static, database, system
        'data_type' => 'string',
        'config' => [
            'value' => '', // for static
            'table' => '', // for database
            'select' => [],
            'filters' => [],
            'sort' => ['field' => '', 'direction' => 'asc'],
            'limit' => 50,
            'source' => '', // for system
        ]
    ];

    public function mount(FormTemplate $template)
    {
        $this->template = $template;
        $this->loadSections();
        $this->loadVariables();
    }

    public function loadVariables()
    {
        $this->variables = $this->template->variables()->orderBy('name')->get();
    }

    public function loadSections()
    {
        $this->sections = $this->template->sections()
            ->with(['fields' => function($q) {
                // Load root fields (no parent) and their children recursively (3 levels)
                $q->whereNull('parent_field_id')
                  ->with([
                      'datasetBinding', 'options', 
                      'children.datasetBinding', 'children.options',
                      'children.children.datasetBinding', 'children.children.options',
                      'children.children.children'
                  ])
                  ->orderBy('order_index');
            }])
            ->get();
    }

    public function addField($sectionId, $parentFieldId = null)
    {
        $this->currentSectionId = $sectionId;
        $this->resetFieldData();
        $this->fieldData['parent_field_id'] = $parentFieldId;
        $this->showFieldModal = true;
    }

    public function editField($fieldId)
    {
        $field = FormField::with('options', 'datasetBinding')->find($fieldId);
        $this->editingFieldId = $fieldId;
        $this->currentSectionId = $field->section_id;
        
        $meta = $field->meta ?? [];
        
        // Ensure image_upload defaults are set for backward compatibility
        if ($field->type === 'image_upload') {
            $meta['max_size'] = $meta['max_size'] ?? 2;
            $meta['allowed_types'] = $meta['allowed_types'] ?? ['jpg', 'jpeg', 'png'];
            $meta['display_width'] = $meta['display_width'] ?? '200px';
            $meta['display_height'] = $meta['display_height'] ?? 'auto';
            $meta['max_width'] = $meta['max_width'] ?? null;
            $meta['max_height'] = $meta['max_height'] ?? null;
        }
        
        // Ensure container-specific defaults are set for backward compatibility
        if ($field->type === 'container') {
            $meta['columns'] = $meta['columns'] ?? 1;
            if (!isset($meta['css'])) {
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
            } else {
                // Merge with defaults to ensure all CSS properties exist
                $defaultCss = [
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
                $meta['css'] = array_merge($defaultCss, $meta['css']);
            }
        }
        
        $this->fieldData = [
            'label' => $field->label,
            'type' => $field->type,
            'required' => (bool)$field->required,
            'options' => $field->options->map(fn($o) => ['label' => $o->label, 'value' => $o->value])->toArray(),
            'dataset_binding' => $field->datasetBinding ? $field->datasetBinding->toArray() : null,
            'meta' => $meta,
            'parent_field_id' => $field->parent_field_id,
        ];
        
        $this->showFieldModal = true;
    }
    
    public function resetFieldData()
    {
        $this->fieldData = [
            'label' => '',
            'type' => 'text',
            'required' => false,
            'parent_field_id' => null,
            'options' => [],
            'dataset_binding' => null,
            'quick_list_items' => [], // for new lists
            'meta' => []
        ];
        // Pre-fill one option for usability
        // $this->fieldData['options'][] = ['label' => 'Option 1', 'value' => 'option_1'];
        $this->editingFieldId = null;
        $this->staticImageUpload = null;
    }

    public function updatedFieldDataType($value)
    {
        // Initialize container defaults when type changes to container
        if ($value === 'container' || $value === 'ul' || $value === 'ol') {
            if ($value === 'container' && !isset($this->fieldData['meta']['columns'])) {
                $this->fieldData['meta']['columns'] = 1;
            }
            if (!isset($this->fieldData['meta']['css'])) {
                $this->fieldData['meta']['css'] = [
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
                    'list_style_type' => '', // For UL/OL
                ];
            }
            if (($value === 'ul' || $value === 'ol') && !isset($this->fieldData['quick_list_items'])) {
                $this->fieldData['quick_list_items'] = [];
            }
        }
        
        // Initialize image_upload defaults when type changes to image_upload
        if ($value === 'image_upload') {
            if (!isset($this->fieldData['meta']['max_size'])) {
                $this->fieldData['meta']['max_size'] = 2;
            }
            if (!isset($this->fieldData['meta']['allowed_types'])) {
                $this->fieldData['meta']['allowed_types'] = ['jpg', 'jpeg', 'png'];
            }
            if (!isset($this->fieldData['meta']['display_width'])) {
                $this->fieldData['meta']['display_width'] = '200px';
            }
            if (!isset($this->fieldData['meta']['display_height'])) {
                $this->fieldData['meta']['display_height'] = 'auto';
            }
        }
    }

    public function addOption()
    {
        $this->fieldData['options'][] = ['label' => '', 'value' => '', 'is_default' => false];
    }

    public function removeOption($index)
    {
        unset($this->fieldData['options'][$index]);
        $this->fieldData['options'] = array_values($this->fieldData['options']);
    }
    
    // Quick List Items
    public function addQuickListItem()
    {
        $this->fieldData['quick_list_items'][] = '';
    }

    public function removeQuickListItem($index)
    {
        unset($this->fieldData['quick_list_items'][$index]);
        $this->fieldData['quick_list_items'] = array_values($this->fieldData['quick_list_items']);
    }

    public function saveField()
    {
        $rules = [
            'fieldData.label' => 'required|string',
            'fieldData.type' => 'required',
        ];
        
        // Add container/list-specific validation
        if (in_array($this->fieldData['type'], ['container', 'ul', 'ol'])) {
            if ($this->fieldData['type'] === 'container') {
                $rules['fieldData.meta.columns'] = 'nullable|integer|min:1|max:12';
            }
            
            // Validate CSS properties if provided
            if (isset($this->fieldData['meta']['css'])) {
                $cssRules = [
                    'fieldData.meta.css.margin_top' => 'nullable|string|max:50',
                    'fieldData.meta.css.margin_right' => 'nullable|string|max:50',
                    'fieldData.meta.css.margin_bottom' => 'nullable|string|max:50',
                    'fieldData.meta.css.margin_left' => 'nullable|string|max:50',
                    'fieldData.meta.css.padding_top' => 'nullable|string|max:50',
                    'fieldData.meta.css.padding_right' => 'nullable|string|max:50',
                    'fieldData.meta.css.padding_bottom' => 'nullable|string|max:50',
                    'fieldData.meta.css.padding_left' => 'nullable|string|max:50',
                    'fieldData.meta.css.background_color' => 'nullable|string|max:50',
                    'fieldData.meta.css.text_color' => 'nullable|string|max:50',
                    'fieldData.meta.css.border_width' => 'nullable|string|max:50',
                    'fieldData.meta.css.border_style' => 'nullable|string|max:50',
                    'fieldData.meta.css.border_color' => 'nullable|string|max:50',
                    'fieldData.meta.css.border_radius' => 'nullable|string|max:50',
                    'fieldData.meta.css.font_family' => 'nullable|string|max:255',
                    'fieldData.meta.css.font_size' => 'nullable|string|max:50',
                    'fieldData.meta.css.font_weight' => 'nullable|string|max:50',
                    'fieldData.meta.css.width' => 'nullable|string|max:50',
                    'fieldData.meta.css.height' => 'nullable|string|max:50',
                    'fieldData.meta.css.box_shadow' => 'nullable|string|max:255',
                    'fieldData.meta.css.display' => 'nullable|string|max:50',
                    'fieldData.meta.css.custom_css' => 'nullable|string|max:5000',
                    'fieldData.meta.css.list_style_type' => 'nullable|string|max:50',
                ];
                $rules = array_merge($rules, $cssRules);
            }
        }
        
        // Add image_upload-specific validation
        if ($this->fieldData['type'] === 'image_upload') {
            $rules['fieldData.meta.max_size'] = 'nullable|numeric|min:0.1|max:20';
            $rules['fieldData.meta.allowed_types'] = 'nullable|array';
            $rules['fieldData.meta.display_width'] = 'nullable|string|max:50';
            $rules['fieldData.meta.display_height'] = 'nullable|string|max:50';
            $rules['fieldData.meta.max_width'] = 'nullable|integer|max:10000';
            $rules['fieldData.meta.max_height'] = 'nullable|integer|max:10000';
        }
        
        // Add static_image_upload-specific validation
        if ($this->fieldData['type'] === 'static_image_upload') {
            $rules['staticImageUpload'] = 'nullable|image|max:5120'; // 5MB max
            $rules['fieldData.meta.width'] = 'nullable|string|max:50';
            $rules['fieldData.meta.height'] = 'nullable|string|max:50';
            $rules['fieldData.meta.alt_text'] = 'nullable|string|max:255';
            $rules['fieldData.meta.alignment'] = 'nullable|string|in:left,center,right';
        }
        
        $this->validate($rules);

        // Handle static image upload
        if ($this->fieldData['type'] === 'static_image_upload') {
            if ($this->staticImageUpload) {
                // New image uploaded
                $fileName = 'static_' . time() . '_' . uniqid() . '.' . $this->staticImageUpload->getClientOriginalExtension();
                $path = $this->staticImageUpload->storeAs('template-images/static', $fileName, 'public');
                $this->fieldData['meta']['image_path'] = $path;
                
                // If editing, delete old image
                if ($this->editingFieldId) {
                    $field = FormField::find($this->editingFieldId);
                    if ($field && isset($field->meta['image_path']) && $field->meta['image_path']) {
                        Storage::disk('public')->delete($field->meta['image_path']);
                    }
                }
            } elseif ($this->editingFieldId) {
                // Editing but no new image - preserve existing image_path
                $field = FormField::find($this->editingFieldId);
                if ($field && isset($field->meta['image_path'])) {
                    $this->fieldData['meta']['image_path'] = $field->meta['image_path'];
                }
            }
        }

        $service = app(TemplateFieldService::class);
        $section = TemplateSection::find($this->currentSectionId);

        if ($this->editingFieldId) {
            $field = FormField::find($this->editingFieldId);
            $service->updateField($field, $this->fieldData);
        } else {
            // New Field
            $this->fieldData['order_index'] = $section->fields()->max('order_index') + 1;
            
            // Create the main field
            $field = $service->addField($section, $this->fieldData);
            
            // Handle Quick List Items (only for new UL/OL fields)
            if (isset($this->fieldData['quick_list_items']) && is_array($this->fieldData['quick_list_items'])) {
                foreach ($this->fieldData['quick_list_items'] as $index => $itemLabel) {
                    if (!empty($itemLabel)) {
                        $childData = [
                            'label' => $itemLabel,
                            'type' => 'paragraph', // Default type for list items
                            'required' => false,
                            'parent_field_id' => $field->id, // Parent is the newly created UL/OL
                            'order_index' => $index,
                            'meta' => [],
                        ];
                        $service->addField($section, $childData);
                    }
                }
            }
        }

        $this->showFieldModal = false;
        $this->staticImageUpload = null;
        $this->loadSections();
        $this->dispatch('fieldSaved'); // Notify frontend
    }

    public function deleteField($fieldId)
    {
        $field = FormField::find($fieldId);
        if ($field) {
            app(TemplateFieldService::class)->deleteField($field);
            $this->loadSections();
        }
    }
    
    public function updateFieldOrder($parentId, $orderedIds)
    {
        // $parentId is the ID of the container/list field, or null if root of section?
        // Actually, if moving between containers, parentId is the NEW parent.
        // If sorting within root section, parentId might be passed as null or section ID?
        // Let's assume parentId is the field ID of the container. If it's a section, we might need a different handling or pass section ID separately.
        // For now, let's support reordering within containers/lists and moving between them.
        // If parentId is "root", it means root of the section.
        
        if (!is_array($orderedIds)) {
            return;
        }

        foreach ($orderedIds as $index => $fieldId) {
            $field = FormField::find($fieldId);
            if ($field) {
                // Update parent and order
                $field->parent_field_id = ($parentId === 'root' || empty($parentId)) ? null : $parentId;
                $field->order_index = $index;
                $field->save();
            }
        }
        
        $this->loadSections();
    }
    
    public function handleDatasetBinding($bindingData)
    {
        // Receive binding data from child component/modal
        $this->fieldData['dataset_binding'] = $bindingData;
    }

    // === Variable Management ===

    public function toggleVariablesPanel()
    {
        $this->showVariablesPanel = !$this->showVariablesPanel;
    }

    public function openVariableModal($variableId = null)
    {
        $this->resetVariableData();
        
        if ($variableId) {
            $variable = $this->template->variables()->find($variableId);
            if ($variable) {
                $this->variableData = [
                    'id' => $variable->id,
                    'name' => $variable->name,
                    'type' => $variable->type,
                    'data_type' => $variable->data_type,
                    'config' => $variable->config ?? [],
                ];
            }
        }
        
        $this->showVariableModal = true;
    }

    public function resetVariableData()
    {
        $this->variableData = [
            'id' => null,
            'name' => '',
            'type' => 'static',
            'data_type' => 'string',
            'config' => [
                'value' => '',
                'table' => '', 
                'select' => [],
                'filters' => [],
                'sort' => ['field' => 'id', 'direction' => 'asc'],
                'limit' => 50, 
                'source' => '',
            ]
        ];
    }

    public function saveVariable()
    {
        $manager = app(VariableManager::class);
        
        $this->validate([
            'variableData.name' => 'required|regex:/^[a-zA-Z0-9_]+$/',
            'variableData.type' => 'required|in:static,database,system',
            'variableData.data_type' => 'required',
        ]);

        try {
            if ($this->variableData['id']) {
                $variable = $this->template->variables()->find($this->variableData['id']);
                $manager->updateVariable($variable, $this->variableData);
            } else {
                $manager->createVariable($this->template, $this->variableData);
            }

            $this->showVariableModal = false;
            $this->loadVariables();
            $this->dispatch('variableSaved'); // Notify frontend
            
        } catch (\Exception $e) {
            $this->addError('variableData.name', $e->getMessage());
        }
    }

    public function deleteVariable($variableId)
    {
        $variable = $this->template->variables()->find($variableId);
        if ($variable) {
            app(VariableManager::class)->deleteVariable($variable);
            $this->loadVariables();
        }
    }

    public function render()
    {
        return view('template-engine::livewire.builder');
    }
}
