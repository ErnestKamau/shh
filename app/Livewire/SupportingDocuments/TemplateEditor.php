<?php

namespace App\Livewire\SupportingDocuments;

use Livewire\Component;
use App\Models\SupportingDocumentTemplate;
use App\Models\SupportingDocumentSection;
use App\Models\SupportingDocumentElement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemplateEditor extends Component
{
    public SupportingDocumentTemplate $templateModel;

    public ?string $document_code = null;
    public string $title = '';
    public ?string $subtitle = null;
    public ?string $description = null;
    public bool $is_active = true;
    public bool $is_published = false;
    public int $version = 1;

    public ?string $message = null;
    public string $messageType = 'success';

    public bool $showAddSection = false;
    public ?string $newSectionTitle = null;

    public bool $showAddElement = false;
    public ?int $addElementSectionId = null;
    public string $newElementType = 'static_text';
    public ?string $newElementLabel = null;
    public ?string $newElementName = null;
    public ?string $newElementPlaceholder = null;
    public ?string $newElementHelpText = null;
    public bool $newElementRequired = false;
    public bool $newElementReadonly = false;
    public ?string $newElementDefaultValue = null;

    public bool $showEditElement = false;
    public ?int $editElementId = null;
    public string $editElementType = 'static_text';
    public ?string $editElementLabel = null;
    public ?string $editElementName = null;
    public ?string $editElementPlaceholder = null;
    public ?string $editElementHelpText = null;
    public bool $editElementRequired = false;
    public bool $editElementReadonly = false;
    public ?string $editElementDefaultValue = null;

    public function mount(string $templateId): void
    {
        $this->templateModel = SupportingDocumentTemplate::with('sections.elements')->findOrFail($templateId);

        $this->document_code = $this->templateModel->document_code;
        $this->title = $this->templateModel->title;
        $this->subtitle = $this->templateModel->subtitle;
        $this->description = $this->templateModel->description;
        $this->is_active = (bool) $this->templateModel->is_active;
        $this->is_published = (bool) $this->templateModel->is_published;
        $this->version = (int) $this->templateModel->version;
    }

    public function refreshTemplate(): void
    {
        $this->templateModel = SupportingDocumentTemplate::with('sections.elements')->findOrFail($this->templateModel->id);
        $this->is_active = (bool) $this->templateModel->is_active;
        $this->is_published = (bool) $this->templateModel->is_published;
        $this->version = (int) $this->templateModel->version;
    }

    public function saveTemplate(): void
    {
        $validated = $this->validate([
            'document_code' => 'nullable|string|max:100',
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_active' => 'boolean',
        ]);

        $this->templateModel->update([
            'document_code' => $validated['document_code'],
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'description' => $validated['description'],
            'is_active' => $validated['is_active'],
        ]);

        $this->message = 'Template saved.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function publish(): void
    {
        DB::transaction(function () {
            $template = SupportingDocumentTemplate::lockForUpdate()->findOrFail($this->templateModel->id);
            if (! $template->is_published) {
                $template->version = (int) $template->version + 1;
            }
            $template->is_published = true;
            $template->save();
        });

        $this->message = 'Template published.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function unpublish(): void
    {
        $this->templateModel->update(['is_published' => false]);
        $this->message = 'Template unpublished.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function openAddSection(): void
    {
        $this->newSectionTitle = null;
        $this->showAddSection = true;
    }

    public function addSection(): void
    {
        $validated = $this->validate([
            'newSectionTitle' => 'nullable|string|max:255',
        ]);

        $nextSort = (int) SupportingDocumentSection::where('supporting_document_template_id', $this->templateModel->id)->max('sort_order');
        $nextSort = $nextSort + 1;

        SupportingDocumentSection::create([
            'supporting_document_template_id' => $this->templateModel->id,
            'title' => $validated['newSectionTitle'],
            'sort_order' => $nextSort,
        ]);

        $this->showAddSection = false;
        $this->message = 'Section added.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function deleteSection(int $sectionId): void
    {
        $section = SupportingDocumentSection::where('supporting_document_template_id', $this->templateModel->id)->findOrFail($sectionId);
        $section->delete();

        $this->message = 'Section deleted.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function openAddElement(int $sectionId): void
    {
        $this->addElementSectionId = $sectionId;
        $this->newElementType = 'static_text';
        $this->newElementLabel = null;
        $this->newElementName = null;
        $this->newElementPlaceholder = null;
        $this->newElementHelpText = null;
        $this->newElementRequired = false;
        $this->newElementReadonly = false;
        $this->newElementDefaultValue = null;
        $this->showAddElement = true;
    }

    public function addElement(): void
    {
        $validated = $this->validate([
            'addElementSectionId' => 'required|integer|exists:supporting_document_sections,id',
            'newElementType' => 'required|string|max:50',
            'newElementLabel' => 'nullable|string|max:255',
            'newElementName' => 'nullable|string|max:150',
            'newElementPlaceholder' => 'nullable|string|max:255',
            'newElementHelpText' => 'nullable|string|max:5000',
            'newElementRequired' => 'boolean',
            'newElementReadonly' => 'boolean',
            'newElementDefaultValue' => 'nullable|string|max:20000',
        ]);

        $section = SupportingDocumentSection::where('supporting_document_template_id', $this->templateModel->id)
            ->findOrFail((int) $validated['addElementSectionId']);

        $nextSort = (int) SupportingDocumentElement::where('supporting_document_section_id', $section->id)->max('sort_order');
        $nextSort = $nextSort + 1;

        SupportingDocumentElement::create([
            'supporting_document_section_id' => $section->id,
            'element_type' => $validated['newElementType'],
            'label' => $validated['newElementLabel'],
            'name' => in_array($validated['newElementType'], ['static_text', 'paragraph_template']) ? null : $validated['newElementName'],
            'placeholder' => $validated['newElementPlaceholder'],
            'help_text' => $validated['newElementHelpText'],
            'is_required' => (bool) $validated['newElementRequired'],
            'is_readonly' => (bool) $validated['newElementReadonly'],
            'default_value' => $validated['newElementDefaultValue'],
            'validation_rules' => null,
            'options' => null,
            'conditional_logic' => null,
            'sort_order' => $nextSort,
        ]);

        $this->showAddElement = false;
        $this->message = 'Element added.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function openEditElement(int $elementId): void
    {
        $element = SupportingDocumentElement::whereHas('section', function ($q) {
            $q->where('supporting_document_template_id', $this->templateModel->id);
        })->findOrFail($elementId);

        $this->editElementId = $element->id;
        $this->editElementType = $element->element_type;
        $this->editElementLabel = $element->label;
        $this->editElementName = $element->name;
        $this->editElementPlaceholder = $element->placeholder;
        $this->editElementHelpText = $element->help_text;
        $this->editElementRequired = (bool) $element->is_required;
        $this->editElementReadonly = (bool) $element->is_readonly;
        $this->editElementDefaultValue = $element->default_value;
        $this->showEditElement = true;
    }

    public function updateElement(): void
    {
        $validated = $this->validate([
            'editElementId' => 'required|integer',
            'editElementType' => 'required|string|max:50',
            'editElementLabel' => 'nullable|string|max:255',
            'editElementName' => 'nullable|string|max:150',
            'editElementPlaceholder' => 'nullable|string|max:255',
            'editElementHelpText' => 'nullable|string|max:5000',
            'editElementRequired' => 'boolean',
            'editElementReadonly' => 'boolean',
            'editElementDefaultValue' => 'nullable|string|max:20000',
        ]);

        $element = SupportingDocumentElement::whereHas('section', function ($q) {
            $q->where('supporting_document_template_id', $this->templateModel->id);
        })->findOrFail((int) $validated['editElementId']);

        $element->update([
            'element_type' => $validated['editElementType'],
            'label' => $validated['editElementLabel'],
            'name' => in_array($validated['editElementType'], ['static_text', 'paragraph_template']) ? null : $validated['editElementName'],
            'placeholder' => $validated['editElementPlaceholder'],
            'help_text' => $validated['editElementHelpText'],
            'is_required' => (bool) $validated['editElementRequired'],
            'is_readonly' => (bool) $validated['editElementReadonly'],
            'default_value' => $validated['editElementDefaultValue'],
        ]);

        $this->showEditElement = false;
        $this->message = 'Element updated.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function deleteElement(int $elementId): void
    {
        $element = SupportingDocumentElement::whereHas('section', function ($q) {
            $q->where('supporting_document_template_id', $this->templateModel->id);
        })->findOrFail($elementId);

        $element->delete();
        $this->message = 'Element deleted.';
        $this->messageType = 'success';
        $this->refreshTemplate();
    }

    public function dismissMessage(): void
    {
        $this->message = null;
    }

    public function render()
    {
        return view('livewire.supporting-documents.template-editor', [
            'sections' => $this->templateModel->sections,
        ]);
    }
}
