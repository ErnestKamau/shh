<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Models\TemplateSection;
use Modules\TemplateEngine\Services\TemplateCreationService;
use Modules\TemplateEngine\Services\TemplateFieldService;
use Modules\TemplateEngine\Models\FormField;

class Builder extends Component
{
    public $template;
    public $sections;
    
    // Modal state
    public $showFieldModal = false;
    public $currentSectionId;
    public $editingFieldId = null;
    
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

    public function mount(FormTemplate $template)
    {
        $this->template = $template;
        $this->loadSections();
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
        
        $this->fieldData = [
            'label' => $field->label,
            'type' => $field->type,
            'required' => (bool)$field->required,
            'options' => $field->options->map(fn($o) => ['label' => $o->label, 'value' => $o->value])->toArray(),
            'dataset_binding' => $field->datasetBinding ? $field->datasetBinding->toArray() : null,
            'meta' => $field->meta ?? [],
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
            'meta' => []
        ];
        // Pre-fill one option for usability
        // $this->fieldData['options'][] = ['label' => 'Option 1', 'value' => 'option_1'];
        $this->editingFieldId = null;
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

    public function saveField()
    {
        $this->validate([
            'fieldData.label' => 'required|string',
            'fieldData.type' => 'required',
        ]);

        $service = app(TemplateFieldService::class);
        $section = TemplateSection::find($this->currentSectionId);

        if ($this->editingFieldId) {
            $field = FormField::find($this->editingFieldId);
            $service->updateField($field, $this->fieldData);
        } else {
            // New Field
            $this->fieldData['order_index'] = $section->fields()->max('order_index') + 1;
            $service->addField($section, $this->fieldData);
        }

        $this->showFieldModal = false;
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
    
    public function handleDatasetBinding($bindingData)
    {
        // Receive binding data from child component/modal
        $this->fieldData['dataset_binding'] = $bindingData;
    }

    public function render()
    {
        return view('template-engine::livewire.builder');
    }
}
