<?php

namespace Modules\TemplateEngine\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TemplateEngine\Services\TemplateFieldService;
use Modules\TemplateEngine\Models\TemplateSection;
use Modules\TemplateEngine\Models\FormField;

class TemplateFieldsController extends Controller
{
    protected $fieldService;

    public function __construct(TemplateFieldService $fieldService)
    {
        $this->fieldService = $fieldService;
    }

    public function store(Request $request, $sectionId)
    {
        $section = TemplateSection::findOrFail($sectionId);
        
        // Validation can be expanded
        $data = $request->validate([
            'label' => 'required|string',
            'type' => 'required|string',
            'options' => 'nullable|array',
            'dataset_binding' => 'nullable|array',
            // other fields...
        ]);

        $field = $this->fieldService->addField($section, $data);

        return response()->json(['message' => 'Field created', 'field' => $field->load('options', 'datasetBinding')]);
    }

    public function update(Request $request, $fieldId)
    {
        $field = FormField::findOrFail($fieldId);
        
        $data = $request->all(); // Validation needed
        
        $updatedField = $this->fieldService->updateField($field, $data);

        return response()->json(['message' => 'Field updated', 'field' => $updatedField->load('options', 'datasetBinding')]);
    }

    public function destroy($fieldId)
    {
        $field = FormField::findOrFail($fieldId);
        $this->fieldService->deleteField($field);

        return response()->json(['message' => 'Field deleted']);
    }
}
