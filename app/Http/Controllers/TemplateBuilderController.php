<?php

namespace App\Http\Controllers;

use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\CertificateTemplateElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TemplateBuilderController extends Controller
{
    /**
     * Show the template builder interface.
     */
    public function builder(CertificateTemplate $certificateTemplate): View
    {
        $template = $certificateTemplate->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.elements' => function ($query) {
                $query->whereNull('certificate_template_element_holder_id')->orderBy('sort_order');
            }
        ]);

        return view('certificate-templates.builder', compact('template'));
    }

    /**
     * Create a new section.
     */
    public function createSection(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_section_id' => 'nullable|exists:certificate_template_sections,id',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $section = CertificateTemplateSection::create([
            'certificate_template_id' => $certificateTemplate->id,
            'parent_section_id' => $request->parent_section_id,
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $request->sort_order ?? 0,
            'is_collapsible' => $request->boolean('is_collapsible', false),
            'styling_options' => $request->styling_options ?? []
        ]);

        return response()->json([
            'success' => true,
            'section' => $section->load('elements'),
            'message' => 'Section created successfully.'
        ]);
    }

    /**
     * Update a section.
     */
    public function updateSection(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_collapsible' => 'boolean',
            'styling_options' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $section->update($request->only([
            'title', 'description', 'sort_order', 'is_collapsible', 'styling_options'
        ]));

        return response()->json([
            'success' => true,
            'section' => $section->fresh(),
            'message' => 'Section updated successfully.'
        ]);
    }

    /**
     * Delete a section.
     */
    public function deleteSection(CertificateTemplateSection $section): JsonResponse
    {
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully.'
        ]);
    }

    /**
     * Create a new element.
     */
    public function createElement(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'element_type' => 'required|in:' . implode(',', array_keys(CertificateTemplateElement::getElementTypes())),
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'styling' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'is_conditional' => 'boolean',
            'conditional_logic' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $element = CertificateTemplateElement::create([
            'certificate_template_section_id' => $section->id,
            'element_type' => $request->element_type,
            'content' => $request->content,
            'properties' => $request->properties ?? [],
            'styling' => $request->styling ?? [],
            'sort_order' => $request->sort_order ?? 0,
            'is_conditional' => $request->boolean('is_conditional', false),
            'conditional_logic' => $request->conditional_logic ?? []
        ]);

        return response()->json([
            'success' => true,
            'element' => $element,
            'message' => 'Element created successfully.'
        ]);
    }

    /**
     * Show an element.
     */
    public function showElement(CertificateTemplateElement $element): JsonResponse
    {
        return response()->json([
            'success' => true,
            'element' => $element->load('section')
        ]);
    }

    /**
     * Update an element.
     */
    public function updateElement(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'styling' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0',
            'is_conditional' => 'boolean',
            'conditional_logic' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $element->update($request->only([
            'content', 'properties', 'styling', 'sort_order', 'is_conditional', 'conditional_logic'
        ]));

        return response()->json([
            'success' => true,
            'element' => $element->fresh(),
            'message' => 'Element updated successfully.'
        ]);
    }

    /**
     * Delete an element.
     */
    public function deleteElement(CertificateTemplateElement $element): JsonResponse
    {
        $element->delete();

        return response()->json([
            'success' => true,
            'message' => 'Element deleted successfully.'
        ]);
    }

    /**
     * Reorder sections.
     */
    public function reorderSections(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:certificate_template_sections,id',
            'sections.*.sort_order' => 'required|integer|min:0',
            'sections.*.parent_section_id' => 'nullable|exists:certificate_template_sections,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request) {
            foreach ($request->sections as $sectionData) {
                CertificateTemplateSection::where('id', $sectionData['id'])
                    ->update([
                        'sort_order' => $sectionData['sort_order'],
                        'parent_section_id' => $sectionData['parent_section_id'] ?? null
                    ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully.'
        ]);
    }

    /**
     * Reorder elements within a section.
     */
    public function reorderElements(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'elements' => 'required|array',
            'elements.*.id' => 'required|exists:certificate_template_elements,id',
            'elements.*.sort_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request) {
            foreach ($request->elements as $elementData) {
                CertificateTemplateElement::where('id', $elementData['id'])
                    ->update(['sort_order' => $elementData['sort_order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Elements reordered successfully.'
        ]);
    }

    /**
     * Get available data fields for data field elements.
     */
    public function getDataFields(Request $request): JsonResponse
    {
        $submissionFormId = $request->get('submission_form_id');
        $dataFields = [];
        
        // Default system fields always available
        $systemFields = [
            'certificate_number' => 'Certificate Number',
            'issue_date' => 'Issue Date',
            'lab_name' => 'Laboratory Name',
            'lab_address' => 'Laboratory Address',
            'lab_phone' => 'Laboratory Phone',
            'lab_email' => 'Laboratory Email',
            'analyst_name' => 'Analyst Name',
            'analyst_signature' => 'Analyst Signature',
            'manager_name' => 'Manager Name',
            'manager_signature' => 'Manager Signature'
        ];
        
        // Add system fields to the list
        foreach ($systemFields as $key => $label) {
            $dataFields[] = [
                'key' => $key,
                'label' => $label,
                'category' => 'System Fields',
                'type' => 'system'
            ];
        }
        
        // Sample-specific fields
        $sampleFields = [
            'sample_code' => 'Sample Code',
            'sample_name' => 'Sample Name',
            'sample_type' => 'Sample Type',
            'sample_description' => 'Sample Description',
            'sampling_date' => 'Sampling Date',
            'received_date' => 'Date Received',
            'analysis_date' => 'Analysis Date',
            'completion_date' => 'Completion Date',
            'client_name' => 'Client Name',
            'client_address' => 'Client Address',
            'client_phone' => 'Client Phone',
            'client_email' => 'Client Email'
        ];
        
        foreach ($sampleFields as $key => $label) {
            $dataFields[] = [
                'key' => $key,
                'label' => $label,
                'category' => 'Sample Information',
                'type' => 'sample'
            ];
        }
        
        // If submission form ID provided, get dynamic fields from form
        if ($submissionFormId) {
            $submissionForm = \App\Models\SubmissionForm::with(['sections.elements'])->find($submissionFormId);
            
            if ($submissionForm) {
                foreach ($submissionForm->sections as $section) {
                    foreach ($section->elements as $element) {
                        if ($element->element_name && $element->element_label) {
                            $dataFields[] = [
                                'key' => 'form_' . $element->element_name,
                                'label' => $element->element_label,
                                'category' => $section->title,
                                'type' => $element->element_type,
                                'section_id' => $section->id,
                                'element_id' => $element->id
                            ];
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'data_fields' => $dataFields
        ]);
    }
}
