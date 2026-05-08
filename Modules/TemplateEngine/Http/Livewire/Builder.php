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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    public $activeVariableStep = 1;
    public $variables = [];
    public $dbTables = [];
    public $availableColumns = [];
    public $tableColumnsCache = [];
    public $selectedVariableColumns = [];
    public $showTestInjectionModal = false;
    public $testInjectionOptions = [];
    public $testInjectionValues = [];
    public $selectedVariableType = 'collection'; // 'collection' or 'single'
    public $injectionColumns = []; // [ index => [columns] ]
    public $queryPreview = null;
    public $queryError = null;
    
    public $variableData = [
        'id' => null,
        'name' => '',
        'type' => 'static', // static, database, system
        'data_type' => 'string',
        'config' => [
            'value' => '', // for static
            'table' => '', // for database
            'select' => [],
            'filters' => [], // Now array of objects
            'joins' => [], // New: Array of join objects
            'group_bys' => [],
            'order_bys' => [],
            'injections' => [],
            'limit' => 50,
            'source' => '', // for system
        ]
    ];



    public function mount(FormTemplate $template)
    {
        $this->template = $template;
        $this->loadSections();
        $this->loadVariables();
        $this->loadTables();
    }

    public function loadTables()
    {
        try {
            $this->dbTables = collect(Schema::getTables())
                ->map(fn ($table) => $table['name'] ?? $table['table_name'] ?? null)
                ->filter()
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            $this->dbTables = [];
        }
        sort($this->dbTables);
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
            'data_sources' => $meta['data_sources'] ?? [],
        ];
        
        // Reconstruct data sources for older fields if empty
        if (empty($this->fieldData['data_sources'])) {
            // If it's a list with children
            if (in_array($field->type, ['ul', 'ol']) && $field->children->count() > 0) {
                foreach($field->children as $child) {
                    $sourceType = 'static';
                    $config = ['label' => $child->label, 'value' => ''];
                    
                    if (str_contains($child->label, '{{')) {
                       // Heuristic: if contains braces, might be variable
                       // Or check meta
                    }
                    if ($child->datasetBinding) {
                        $sourceType = 'dynamic';
                        $config['dataset_binding'] = $child->datasetBinding->toArray();
                    }
                    
                    $this->fieldData['data_sources'][] = [
                        'type' => $sourceType,
                        'config' => $config,
                        'order' => count($this->fieldData['data_sources'])
                    ];
                }
            }
            // If it's options
            elseif (count($field->options) > 0) {
                 foreach($field->options as $opt) {
                     $this->fieldData['data_sources'][] = [
                        'type' => 'static',
                        'config' => ['label' => $opt->label, 'value' => $opt->value],
                        'order' => count($this->fieldData['data_sources'])
                    ];
                 }
            }
        }
        
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
            'quick_list_items' => [], // Legacy: keep for now
            'data_sources' => [], // New: Multi-source support
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
            
            // Initialize data sources for Lists
            if (($value === 'ul' || $value === 'ol') && !isset($this->fieldData['data_sources'])) {
                $this->fieldData['data_sources'] = [];
            }
        }
        
        // Initialize data sources for Option types
        if (in_array($value, ['select', 'radio', 'checkbox'])) {
             if (!isset($this->fieldData['data_sources'])) {
                $this->fieldData['data_sources'] = [];
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
            if (!isset($this->fieldData['meta']['display_height'])) {
                $this->fieldData['meta']['display_height'] = 'auto';
            }
        }
        
        // Initialize Defaults for Dynamic Table
        if ($value === 'dynamic_table') {
            if (!isset($this->fieldData['meta']['headers'])) {
                $this->fieldData['meta']['headers'] = [
                    ['label' => 'Header 1', 'width' => '', 'style' => '']
                ];
            }
            if (!isset($this->fieldData['meta']['rows'])) {
                $this->fieldData['meta']['rows'] = [
                    [
                        'type' => 'static', // static, variable
                        'source' => '', // variable_id if type is variable
                        'cells' => [
                            ['content' => 'Cell 1', 'style' => '', 'colspan' => 1]
                        ]
                    ]
                ];
            }
            // Default Config Styles
            if (!isset($this->fieldData['meta']['css'])) {
                $this->fieldData['meta']['css'] = [
                    'width' => '100%',
                    'border_collapse' => 'collapse',
                    'class' => 'table table-bordered'
                ];
            }
        }
    }

    // === Data Source Management ===

    public function addDataSource($type = 'static')
    {
        if (!isset($this->fieldData['data_sources'])) {
            $this->fieldData['data_sources'] = [];
        }

        $config = [];
        if ($type === 'static') {
            $config = ['label' => '', 'value' => '']; // Value used for options, label for lists
        } elseif ($type === 'variable') {
            $config = ['variable_id' => '', 'column' => '', 'template' => ''];
        } elseif ($type === 'dynamic') {
            $config = ['dataset_binding' => null];
        }

        $this->fieldData['data_sources'][] = [
            'type' => $type,
            'config' => $config,
            'order' => count($this->fieldData['data_sources'])
        ];
    }

    public function removeDataSource($index)
    {
        unset($this->fieldData['data_sources'][$index]);
        $this->fieldData['data_sources'] = array_values($this->fieldData['data_sources']);
    }

    public function moveDataSource($index, $direction)
    {
        $sources = $this->fieldData['data_sources'];
        if ($direction === 'up' && $index > 0) {
            $temp = $sources[$index];
            $sources[$index] = $sources[$index - 1];
            $sources[$index - 1] = $temp;
        } elseif ($direction === 'down' && $index < count($sources) - 1) {
            $temp = $sources[$index];
            $sources[$index] = $sources[$index + 1];
            $sources[$index + 1] = $temp;
        }
        $this->fieldData['data_sources'] = $sources;
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
        if ($this->selectedVariableType === 'single') {
             $this->fieldData['quick_list_items'][] = ['label' => '', 'column' => ''];
        } else {
             $this->fieldData['quick_list_items'][] = '';
        }
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
            
            // Handle Quick List Items (Legacy)
            if (isset($this->fieldData['quick_list_items']) && is_array($this->fieldData['quick_list_items']) && !isset($this->fieldData['data_sources'])) {
                foreach ($this->fieldData['quick_list_items'] as $index => $item) {
                    $childLabel = '';
                    $childMeta = [];

                    if (is_array($item)) {
                        // Handle Structured Item (Label + Column)
                        $prefix = $item['label'] ?? '';
                        $column = $item['column'] ?? '';
                        
                        if (!empty($column)) {
                             $childLabel = trim($prefix . ' {{ ' . $column . ' }}');
                             $childMeta['variable_column'] = $column;
                        } else {
                             $childLabel = $prefix;
                        }
                    } else {
                        // Handle Simple String Item
                        $childLabel = $item;
                    }

                    if (!empty($childLabel)) {
                        $childData = [
                            'label' => $childLabel,
                            'type' => 'paragraph', // Default type for list items
                            'required' => false,
                            'parent_field_id' => $field->id, // Parent is the newly created UL/OL
                            'order_index' => $index,
                            'meta' => $childMeta,
                        ];
                        $service->addField($section, $childData);
                    }
                }
            }

            // Handle New Data Sources (Multi-Source for Lists & Options)
            if (isset($this->fieldData['data_sources']) && is_array($this->fieldData['data_sources'])) {
                // Determine Field Mode (List or Options)
                $isList = in_array($field->type, ['ul', 'ol']);
                
                // If Options based, we might need to store the config in meta instead of creating child fields?
                // Actually, for consistency, options are strictly Meta or Related Table.
                // CURRENT ARCHITECTURE: 'options' are a separate generic relationship or JSON. 
                // Let's check 'options' handling in `editField`. It maps `$field->options`.
                // So options are stored in `form_field_options` table probably.
                // WE NEED TO DECIDE: Do we store complex sources in `meta` and resolve at runtime, OR do we "compile" them?
                // For Lists (UL/OL): We DO create child fields (paragraphs/etc).
                // For Options (Select/Radio): We likely need to store the configuration in `meta` so the renderer can built the options dynamically.
                
                if ($isList) {
                    // For Lists: Create Child Fields
                    foreach ($this->fieldData['data_sources'] as $index => $source) {
                        $sourceType = $source['type'] ?? 'static';
                        $config = $source['config'] ?? [];
                        
                        // == STATIC SOURCE ==
                        if ($sourceType === 'static') {
                            $label = $config['label'] ?? '';
                            if (!empty($label)) {
                                $service->addField($section, [
                                    'label' => $label,
                                    'type' => 'paragraph',
                                    'required' => false,
                                    'parent_field_id' => $field->id,
                                    'order_index' => $index,
                                    'meta' => ['source_type' => 'static']
                                ]);
                            }
                        }
                        
                        // == VARIABLE SOURCE ==
                        elseif ($sourceType === 'variable') {
                            $varId = $config['variable_id'] ?? null;
                            if ($varId) {
                                $variable = collect($this->variables)->firstWhere('id', $varId);
                                if ($variable) {
                                    // Detect if Single Record or Collection
                                    $isSingle = false;
                                    if ($variable->type === 'database' && ($variable->config['limit'] ?? 50) == 1) {
                                        $isSingle = true;
                                    } elseif ($variable->type === 'static' || $variable->type === 'system') {
                                        // Assume static/system might be single string, or check data_type
                                        $isSingle = ($variable->data_type !== 'collection');
                                    }

                                    if ($isSingle) {
                                        // Single Record: Just mapped text
                                        $labelTemplate = $config['template'] ?? ''; // User entered template
                                        // Fallback to column selection if template empty
                                        if (empty($labelTemplate) && !empty($config['column'])) {
                                            $labelTemplate = '{{ ' . $config['column'] . ' }}';
                                        }
                                        
                                        // Prepend Label Prefix if exists
                                        $prefixLabel = $config['label_prefix'] ?? ''; // Renamed from 'label' to avoid confusion with internal field label
                                        if (!empty($prefixLabel)) {
                                            $finalLabel = $prefixLabel . ' ' . $labelTemplate;
                                        } else {
                                            $finalLabel = $labelTemplate;
                                        }
                                        
                                        $service->addField($section, [
                                            'label' => $finalLabel,
                                            'type' => 'paragraph',
                                            'required' => false,
                                            'parent_field_id' => $field->id,
                                            'order_index' => $index,
                                            'meta' => ['source_type' => 'variable_single']
                                        ]);
                                    } else {
                                        // Collection: Repeater
                                        // We create a child field that has a dataset binding OR a variable binding
                                        // Since we don't have a "variable binding" field prop yet, let's look at `dataset_binding`.
                                        // But this is a VARIABLE, not a raw Dataset.
                                        // Solution: Create a child with meta pointing to the variable loop.
                                        $service->addField($section, [
                                            'label' => $config['template'] ?? '{{ item }}',
                                            'type' => 'paragraph',
                                            'required' => false,
                                            'parent_field_id' => $field->id,
                                            'order_index' => $index,
                                            'meta' => [
                                                'source_type' => 'variable_collection',
                                                'variable_id' => $varId,
                                                'loop_variable' => true
                                            ]
                                        ]);
                                    }
                                }
                            }
                        }
                        
                        // == DYNAMIC (Dataset) SOURCE ==
                        elseif ($sourceType === 'dynamic') {
                             $datasetBinding = $config['dataset_binding'] ?? null;
                             if ($datasetBinding) {
                                 // Create a child that acts as the template for the loop
                                 $childWithBinding = $service->addField($section, [
                                     'label' => '{{ value }}', // Default, should be configurable in source manager
                                     'type' => 'paragraph',
                                     'required' => false,
                                     'parent_field_id' => $field->id,
                                     'order_index' => $index,
                                     'meta' => ['source_type' => 'dataset']
                                 ]);
                                 
                                 // Save Binding
                                 // We need to attach the binding to this child field
                                 // The service might not do this automatically if we didn't pass it under `dataset_binding` key
                                 // So we do it manually:
                                 $childWithBinding->datasetBinding()->create([
                                     'dataset_type' => 'table', // Assuming table for now, or get from binding
                                     'table_name' => $datasetBinding['table'] ?? '',
                                     'columns' => $datasetBinding['columns'] ?? ['*'],
                                     'filters' => $datasetBinding['filters'] ?? [],
                                     'limit' => $datasetBinding['limit'] ?? null,
                                     'order_by' => $datasetBinding['order_by'] ?? null,
                                 ]);
                             }
                        }
                    }
                } else {
                    // For Options (Select/Radio): Save Config to Meta
                    // We merge this into the field's meta
                    $newMeta = $field->meta ?? [];
                    $newMeta['data_sources'] = $this->fieldData['data_sources'];
                    $field->meta = $newMeta;
                    $field->save();
                    
                    // Also, for static options, we should probably sync them to the `options` relationship for backward compatibility/DB integrity
                    // Clear existing options
                    $field->options()->delete();
                    
                    foreach ($this->fieldData['data_sources'] as $source) {
                        if (($source['type'] ?? 'static') === 'static') {
                            $field->options()->create([
                                'label' => $source['config']['label'] ?? '',
                                'value' => $source['config']['value'] ?? '',
                            ]);
                        }
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
        // Check if binding key is provided (format: object.key or key)
        // Usually passed as $params in emitting event, but here we just get $bindingData as first arg.
        // Livewire event args: listener($payload, ...$params)
        // We need to know WHICH source triggered this.
        // We can use a property to track "binding target".
        
        if (isset($this->activeBindingTarget) && str_starts_with($this->activeBindingTarget, 'data_source.')) {
            // "data_source.2"
            $parts = explode('.', $this->activeBindingTarget);
            $index = $parts[1] ?? null;
            
            if ($index !== null && isset($this->fieldData['data_sources'][$index])) {
                $this->fieldData['data_sources'][$index]['config']['dataset_binding'] = $bindingData;
            }
            $this->activeBindingTarget = null; // Reset
        } else {
            // Default field binding
            $this->fieldData['dataset_binding'] = $bindingData;
        }
    }
    
    public $activeBindingTarget = null;
    
    public function setBindingTarget($target)
    {
        $this->activeBindingTarget = $target;
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

                // Ensure injections array exists
                if (!isset($this->variableData['config']['injections'])) {
                    $this->variableData['config']['injections'] = [];
                }

                // Load columns for existing injections
                if (!empty($this->variableData['config']['injections'])) {
                    foreach($this->variableData['config']['injections'] as $index => $injection) {
                        if (!empty($injection['table'])) {
                            try {
                                $this->injectionColumns[$index] = Schema::getColumnListing($injection['table']);
                            } catch (\Exception $e) {
                                $this->injectionColumns[$index] = [];
                            }
                        }
                    }
                }

                // Legacy Filter conversion (if previously missed or new logic)
                if (isset($this->variableData['config']['filters']) && is_string($this->variableData['config']['filters'])) {   }
            }
        }
        
        $this->loadAvailableColumns();
        $this->showVariableModal = true;
    }

    public function updatedVariableDataConfigTable($value)
    {
        $this->loadAvailableColumns();
    }

    public function updated($propertyName)
    {
        // Check if a join table was selected
        if (str_starts_with($propertyName, 'variableData.config.joins') && str_ends_with($propertyName, 'table')) {
            $this->loadAvailableColumns();
        }

        // Check if an injection table was selected
        if (str_starts_with($propertyName, 'variableData.config.injections') && str_ends_with($propertyName, 'table')) {
            $this->loadInjectionColumns();
        }

        // Check if Variable ID changed in Field Modal
        if ($propertyName === 'fieldData.meta.variable_id') {
            $this->handleFieldVariableChange();
        }
    }

    public function handleFieldVariableChange()
    {
        $variableId = $this->fieldData['meta']['variable_id'] ?? null;
        $this->selectedVariableColumns = [];
        $this->selectedVariableType = 'collection';

        if (!$variableId) return;

        $variable = collect($this->variables)->firstWhere('id', $variableId);
        if (!$variable) return;

        // Determine Type (Single vs Collection)
        if ($variable->type === 'database') {
             $limit = $variable->config['limit'] ?? 50;
             if ($limit == 1) {
                 $this->selectedVariableType = 'single';
             }
        }

        // Load Columns
        if ($variable->type === 'database') {
            $mainTable = $variable->config['table'] ?? null;
            if ($mainTable) {
                try {
                    // Get Main Table Columns
                    $columns = Schema::getColumnListing($mainTable);
                    foreach($columns as $col) {
                        $this->selectedVariableColumns[] = $mainTable . '.' . $col;
                    }
                    
                    // Get Join Columns
                    if (!empty($variable->config['joins'])) {
                        foreach($variable->config['joins'] as $join) {
                            if (!empty($join['table'])) {
                                $joinCols = Schema::getColumnListing($join['table']);
                                foreach($joinCols as $col) {
                                    $this->selectedVariableColumns[] = $join['table'] . '.' . $col;
                                }
                            }
                        }
                    }

                    // Get Injection Columns
                    if (!empty($variable->config['injections'])) {
                        foreach($variable->config['injections'] as $injection) {
                            if (!empty($injection['table'])) {
                                $injectionCols = Schema::getColumnListing($injection['table']);
                                foreach($injectionCols as $col) {
                                    $this->selectedVariableColumns[] = $injection['table'] . '.' . $col;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // Silent fail
                }
            }
        }
    }

    public function loadAvailableColumns()
    {
        $this->availableColumns = [];
        $config = $this->variableData['config'];
        $tables = [];

        // Main Table
        if (!empty($config['table'])) {
            $tables[] = $config['table'];
        }

        // Joined Tables
        if (isset($config['joins']) && is_array($config['joins'])) {
            foreach ($config['joins'] as $join) {
                if (!empty($join['table'])) {
                    $tables[] = $join['table'];
                }
            }
        }

        // Injected Tables
        if (isset($config['injections']) && is_array($config['injections'])) {
            foreach ($config['injections'] as $injection) {
                if (!empty($injection['table'])) {
                    $tables[] = $injection['table'];
                }
            }
        }

        // Fetch columns for each table and populate cache
        foreach (array_unique($tables) as $table) {
            if (!isset($this->tableColumnsCache[$table])) {
                try {
                   $this->tableColumnsCache[$table] = Schema::getColumnListing($table);
                } catch (\Exception $e) {
                   $this->tableColumnsCache[$table] = [];
                }
            }
            
            foreach ($this->tableColumnsCache[$table] as $column) {
               $this->availableColumns[] = "{$table}.{$column}";
            }
        }
    }

    public function getColumnsForJoinFirst($joinIndex)
    {
        $columns = [];
        
        // Main Table
        $mainTable = $this->variableData['config']['table'] ?? null;
        if ($mainTable && isset($this->tableColumnsCache[$mainTable])) {
            foreach ($this->tableColumnsCache[$mainTable] as $col) {
                $columns[] = "{$mainTable}.{$col}";
            }
        }

        // Previous Joins
        if (isset($this->variableData['config']['joins'])) {
            foreach ($this->variableData['config']['joins'] as $idx => $join) {
                if ($idx < $joinIndex) {
                    $table = $join['table'] ?? null;
                    if ($table && isset($this->tableColumnsCache[$table])) {
                        foreach ($this->tableColumnsCache[$table] as $col) {
                            $columns[] = "{$table}.{$col}";
                        }
                    }
                }
            }
        }
        
        return $columns;
    }

    public function getSelectOptions()
    {
        $options = [];
        
        // Global Wildcard
        $options[] = '*';

        // Table Wildcards
        $tables = [];
        if (!empty($this->variableData['config']['table'])) {
            $tables[] = $this->variableData['config']['table'];
        }
        if (isset($this->variableData['config']['joins'])) {
            foreach ($this->variableData['config']['joins'] as $join) {
                if (!empty($join['table'])) {
                    $tables[] = $join['table'];
                }
            }
        }
        foreach (array_unique($tables) as $table) {
            $options[] = "{$table}.*";
        }

        // Standard Columns
        return array_merge($options, $this->availableColumns);
    }

    public function getColumnsForJoinSecond($joinIndex)
    {
        $columns = [];
        $join = $this->variableData['config']['joins'][$joinIndex] ?? null;
        
        if ($join) {
            $table = $join['table'] ?? null;
            if ($table && isset($this->tableColumnsCache[$table])) {
                 foreach ($this->tableColumnsCache[$table] as $col) {
                    $columns[] = "{$table}.{$col}";
                }
            }
        }
        
        return $columns;
    }

    public function loadInjectionColumns()
    {
        $this->injectionColumns = [];
        if (isset($this->variableData['config']['injections']) && is_array($this->variableData['config']['injections'])) {
            foreach ($this->variableData['config']['injections'] as $index => $injection) {
                if (!empty($injection['table'])) {
                    try {
                        $this->injectionColumns[$index] = Schema::getColumnListing($injection['table']);
                    } catch (\Exception $e) {
                        $this->injectionColumns[$index] = [];
                    }
                } else {
                    $this->injectionColumns[$index] = [];
                }
            }
        }
    }

    public function addJoin()
    {
        if (!isset($this->variableData['config']['joins'])) {
            $this->variableData['config']['joins'] = [];
        }
        $this->variableData['config']['joins'][] = [
            'table' => '',
            'type' => 'inner',
            'on_first' => '',
            'operator' => '=',
            'on_second' => ''
        ];
    }
    
    // Trigger load available columns when join table changes requires a bit more complex binding or just a button refresh.
    // For simplicity, we'll try to hook into the array update if possible or rely on the user refreshing/saving.
    // But better: Let's make a method updatedVariableDataConfigJoins to reload.
    public function updatedVariableDataConfigJoins()
    {
        $this->loadAvailableColumns();
    }

    public function removeJoin($index)
    {
        unset($this->variableData['config']['joins'][$index]);
        $this->variableData['config']['joins'] = array_values($this->variableData['config']['joins']);
        $this->loadAvailableColumns();
    }

    public function addInjection()
    {
        if (!isset($this->variableData['config']['injections'])) {
            $this->variableData['config']['injections'] = [];
        }
        $this->variableData['config']['injections'][] = [
            'label' => '',
            'table' => '',
            'column' => '',
        ];
        $this->loadInjectionColumns();
    }

    public function removeInjection($index)
    {
        unset($this->variableData['config']['injections'][$index]);
        $this->variableData['config']['injections'] = array_values($this->variableData['config']['injections']);
        $this->loadInjectionColumns();
        $this->loadAvailableColumns(); // Also reload main available columns as an injection might have been removed
    }

    public function addFilter()
    {
        // If filters is still a string (legacy JSON), clear it or convert
        if (is_string($this->variableData['config']['filters'])) {
            $this->variableData['config']['filters'] = [];
        }
        
        $this->variableData['config']['filters'][] = [
            'field' => '',
            'operator' => '=',
            'value' => ''
        ];
    }

    public function removeFilter($index)
    {
        unset($this->variableData['config']['filters'][$index]);
        $this->variableData['config']['filters'] = array_values($this->variableData['config']['filters']);
    }

    public function addSelectField()
    {
        $this->variableData['config']['select'][] = '';
    }

    public function removeSelectField($index)
    {
        unset($this->variableData['config']['select'][$index]);
        $this->variableData['config']['select'] = array_values($this->variableData['config']['select']);
    }

    public function addGroupBy()
    {
        if (!isset($this->variableData['config']['group_bys'])) {
            $this->variableData['config']['group_bys'] = [];
        }
        $this->variableData['config']['group_bys'][] = '';
    }

    public function removeGroupBy($index)
    {
        unset($this->variableData['config']['group_bys'][$index]);
        $this->variableData['config']['group_bys'] = array_values($this->variableData['config']['group_bys']);
    }

    public function addOrderBy()
    {
        if (!isset($this->variableData['config']['order_bys'])) {
            $this->variableData['config']['order_bys'] = [];
        }
        $this->variableData['config']['order_bys'][] = ['field' => '', 'direction' => 'asc'];
    }

    public function removeOrderBy($index)
    {
        unset($this->variableData['config']['order_bys'][$index]);
        $this->variableData['config']['order_bys'] = array_values($this->variableData['config']['order_bys']);
    }

    public function prepareTestQuery()
    {
        $this->testInjectionOptions = [];
        $hasInjections = false;

        if (isset($this->variableData['config']['injections']) && is_array($this->variableData['config']['injections'])) {
            $injections = $this->variableData['config']['injections'];
            
            // Filter out empty injections
            $validInjections = array_filter($injections, function($inj) {
                return !empty($inj['label']) && !empty($inj['table']);
            });

            if (count($validInjections) > 0) {
                $hasInjections = true;
                foreach ($validInjections as $inj) {
                    $table = $inj['table'];
                    $label = $inj['label'];
                    
                    // Initial fetch or use existing if already set? Let's refresh options
                    try {
                        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
                        $displayCol = 'id';
                        foreach(['name', 'title', 'code', 'label', 'description', 'email', 'slug'] as $g) {
                            if (in_array($g, $columns)) {
                                $displayCol = $g;
                                break;
                            }
                        }
                        
                        // Select ID and Display Column
                        $rows = \Illuminate\Support\Facades\DB::table($table)
                                ->select('id', $displayCol)
                                ->limit(50)
                                ->get();
                                
                        $options = [];
                        foreach ($rows as $row) {
                            $options[$row->id] = $row->$displayCol . " (ID: {$row->id})";
                        }
                        
                        $this->testInjectionOptions[$label] = $options;
                    } catch (\Exception $e) {
                         $this->testInjectionOptions[$label] = [];
                    }
                }
            }
        }
        
        if ($hasInjections) {
            $this->showTestInjectionModal = true;
        } else {
            $this->testQuery();
        }
    }

    public function cancelTestInjection()
    {
        $this->showTestInjectionModal = false;
        $this->testInjectionOptions = [];
        $this->testInjectionValues = [];
    }

    public function runTestQueryWithInjections()
    {
        $this->showTestInjectionModal = false;
        $this->testQuery();
    }

    public function testQuery()
    {
        $this->queryPreview = null;
        $this->queryError = null;
        
        $config = $this->variableData['config'];
        $table = $config['table'] ?? null;
        
        if (!$table) {
            $this->queryError = "Please select a database table.";
            return;
        }
        
        try {
            $query = DB::table($table);
            
            // Apply Joins
            if (isset($config['joins']) && is_array($config['joins'])) {
                foreach ($config['joins'] as $join) {
                    if (!empty($join['table']) && !empty($join['on_first']) && !empty($join['on_second'])) {
                        $type = $join['type'] ?? 'inner';
                        $method = $type === 'left' ? 'leftJoin' : ($type === 'right' ? 'rightJoin' : 'join');
                        $query->$method($join['table'], $join['on_first'], $join['operator'] ?? '=', $join['on_second']);
                    }
                }
            }

            // Injections are parameters, not joins usually. 
            
            // Apply Selects
            if (!empty($config['select']) && is_array($config['select'])) {
                $selects = array_filter($config['select']);
                if (!empty($selects)) {
                     // Check for table.* pattern
                     $normalizedSelects = [];
                     foreach($selects as $sel) {
                         if (empty($sel)) continue;
                         // If string, pass through. In Builder we might support simple strings.
                         $normalizedSelects[] = $sel; 
                     }
                     if(!empty($normalizedSelects)) {
                        $query->select($normalizedSelects);
                     } else {
                        $query->select($table.'.*'); 
                     }
                } else {
                    $query->select($table.'.*');
                }
            } else {
                 $query->select($table.'.*');
            }

            // Apply Filters
            if (isset($config['filters'])) {
                $filters = $config['filters'];
                if (is_string($filters)) {
                    $jsonFilters = json_decode($filters, true);
                    if (is_array($jsonFilters)) {
                        $filters = $jsonFilters;
                    }
                }
                
                if (is_array($filters)) {
                    foreach ($filters as $filter) {
                        if (!empty($filter['field'])) {
                            $val = $filter['value'];
                            if (isset($filter['value_source']) && $filter['value_source'] === 'injection') {
                                // Dynamic Injection Logic
                                $injectionLabel = $val; // In this case 'value' holds the label
                                if (isset($this->testInjectionValues[$injectionLabel])) {
                                    $val = $this->testInjectionValues[$injectionLabel];
                                } else {
                                    // Fallback or warning?
                                    // For preview, if no value provided, maybe default to 0 or NULL to avoid query error?
                                    // Or let it run and fail/return empty.
                                    // Let's leave $val as is (the label) which will likely result in 0 rows or conversion error 
                                    // unless the column is string. 
                                    // Actually, better to use NULL or an obvious dummy if valid type.
                                }
                            }
                            $query->where($filter['field'], $filter['operator'] ?? '=', $val);
                        }
                    }
                }
            }

            // Apply Group By
            if (isset($config['group_bys']) && is_array($config['group_bys'])) {
                $groups = array_filter($config['group_bys']);
                if (!empty($groups)) {
                    $query->groupBy(array_values($groups));
                }
            }

            // Apply Order By
            if (isset($config['order_bys']) && is_array($config['order_bys'])) {
                 foreach($config['order_bys'] as $sort) {
                     if (!empty($sort['field'])) {
                         $query->orderBy($sort['field'], $sort['direction'] ?? 'asc');
                     }
                 }
            } elseif (isset($config['sort']) && !empty($config['sort']['field'])) {
                // Legacy Fallback
                $query->orderBy($config['sort']['field'], $config['sort']['order'] ?? 'asc');
            }
            
            // Apply Limit
            $limit = isset($config['limit']) ? (int)$config['limit'] : 50;
            if ($limit > 100) $limit = 100; // Protection cap for preview
            
            $this->queryPreview = $query->limit($limit)->get()->toArray();
            
            // Convert objects to arrays for view compatibility if needed
            $this->queryPreview = array_map(function($item) {
                return (array)$item;
            }, $this->queryPreview);
            
        } catch (\Exception $e) {
            $this->queryError = "SQL Error: " . $e->getMessage();
            $this->queryPreview = null;
        }
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
                'joins' => [],
                'group_bys' => [],
                'order_bys' => [],
                'injections' => [],
                'limit' => 50, 
                'source' => '',
            ]
        ];
        $this->injectionColumns = [];
        $this->queryPreview = null;
        $this->queryError = null;
        $this->activeVariableStep = 1;
    }

    public function setVariableStep($step)
    {
        $this->activeVariableStep = $step;
    }

    public function nextVariableStep()
    {
        if ($this->activeVariableStep === 1) {
            $this->validate([
                'variableData.name' => 'required|regex:/^[a-zA-Z0-9_]+$/',
                'variableData.type' => 'required',
                'variableData.data_type' => 'required',
            ]);
        }
        $this->activeVariableStep++;
    }

    public function prevVariableStep()
    {
        if ($this->activeVariableStep > 1) {
            $this->activeVariableStep--;
        }
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

    public function getExpandedVariableColumns($variableId)
    {
        $columns = [];
        $variable = collect($this->variables)->firstWhere('id', $variableId);
        
        if ($variable && $variable->type === 'database' && isset($variable->config['select']) && is_array($variable->config['select'])) {
            // Check for wildcards
            foreach ($variable->config['select'] as $sel) {
                if (empty($sel)) continue;
                
                if (str_ends_with($sel, '.*')) {
                    // It's a table wildcard
                    $tableName = substr($sel, 0, -2);
                    // Use schema if possible or cache
                    try {
                        if (isset($this->tableColumnsCache[$tableName])) {
                            $cols = $this->tableColumnsCache[$tableName];
                        } else {
                            $cols = Schema::getColumnListing($tableName);
                            $this->tableColumnsCache[$tableName] = $cols; // Cache it
                        }
                        
                        foreach ($cols as $c) {
                            $columns[] = "{$tableName}.{$c}";
                        }
                    } catch (\Throwable $e) {
                         // Fallback: just add item itself if schema fails
                         $columns[] = $sel; 
                    }
                } else {
                    $columns[] = $sel;
                }
            }
        }
        
        return array_unique($columns); // Remove duplicates
    }

    public function render()
    {
        return view('template-engine::livewire.builder');
    }
    // === Dynamic Table Management ===

    public function addTableHeader()
    {
        $this->fieldData['meta']['headers'][] = ['label' => 'New Header', 'width' => '', 'style' => ''];
        
        // Add a corresponding cell to each existing row to maintain structure
        if (isset($this->fieldData['meta']['rows'])) {
            foreach ($this->fieldData['meta']['rows'] as $index => $row) {
                 $this->fieldData['meta']['rows'][$index]['cells'][] = ['content' => '', 'style' => '', 'colspan' => 1];
            }
        }
    }

    public function removeTableHeader($index)
    {
        unset($this->fieldData['meta']['headers'][$index]);
        $this->fieldData['meta']['headers'] = array_values($this->fieldData['meta']['headers']);
        
        // Remove corresponding cell from each row?
        // This might be destructive if not careful, but generally expected in a grid.
        if (isset($this->fieldData['meta']['rows'])) {
            foreach ($this->fieldData['meta']['rows'] as $rIndex => $row) {
                 if (isset($row['cells'][$index])) {
                     unset($this->fieldData['meta']['rows'][$rIndex]['cells'][$index]);
                     $this->fieldData['meta']['rows'][$rIndex]['cells'] = array_values($this->fieldData['meta']['rows'][$rIndex]['cells']);
                 }
            }
        }
    }

    public function addTableRow()
    {
        // Calculate number of cells needed based on headers
        $cellCount = count($this->fieldData['meta']['headers'] ?? []);
        $cells = [];
        for ($i = 0; $i < $cellCount; $i++) {
            $cells[] = ['content' => '', 'style' => '', 'colspan' => 1];
        }

        $this->fieldData['meta']['rows'][] = [
            'type' => 'static',
            'source' => '',
            'cells' => $cells // Initialize with empty cells matching headers
        ];
    }

    public function removeTableRow($index)
    {
        unset($this->fieldData['meta']['rows'][$index]);
        $this->fieldData['meta']['rows'] = array_values($this->fieldData['meta']['rows']);
    }

    public function updateTableCell($rowIndex, $cellIndex, $key, $value)
    {
        $this->fieldData['meta']['rows'][$rowIndex]['cells'][$cellIndex][$key] = $value;
    }
