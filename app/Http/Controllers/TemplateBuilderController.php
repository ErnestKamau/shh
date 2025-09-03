<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateSection;
use App\Models\CertificateTemplateElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class TemplateBuilderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display the template builder interface
     */
    public function index(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('build', $certificateTemplate);
        $certificateTemplate->load([
            'sections.elements' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.children.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('certificate-templates.builder', compact('certificateTemplate'));
    }

    /**
     * Add a new section to the template
     * 
     * @param Request $request
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSection(Request $request, CertificateTemplate $certificateTemplate)
    {
        $this->authorize('build', $certificateTemplate);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_section_id' => 'nullable|integer|exists:certificate_template_sections,id',
            'is_collapsible' => 'boolean',
            'styling_options' => 'nullable|array'
        ]);

        try {
            // Validate parent section belongs to this template and nesting level
            if ($validated['parent_section_id']) {
                $parentSection = CertificateTemplateSection::where('id', $validated['parent_section_id'])
                    ->where('certificate_template_id', $certificateTemplate->id)
                    ->first();
                
                if (!$parentSection) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid parent section'
                    ], 400);
                }

                if (!$parentSection->canHaveChildren()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Maximum nesting level (3 levels) exceeded'
                    ], 400);
                }
            }

            $section = $certificateTemplate->sections()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'parent_section_id' => $validated['parent_section_id'] ?? null,
                'is_collapsible' => $validated['is_collapsible'] ?? false,
                'styling_options' => $validated['styling_options'] ?? null,
                'sort_order' => CertificateTemplateSection::getNextSortOrder(
                    $certificateTemplate->id, 
                    $validated['parent_section_id'] ?? null
                )
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section added successfully',
                'section' => $section->load('elements', 'children')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing section
     * 
     * @param Request $request
     * @param CertificateTemplateSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSection(Request $request, CertificateTemplateSection $section)
    {
        $this->authorize('build', $section->certificateTemplate);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_section_id' => 'nullable|integer|exists:certificate_template_sections,id',
            'is_collapsible' => 'boolean',
            'styling_options' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Validate parent section change if provided
            if (isset($validated['parent_section_id']) && $validated['parent_section_id'] !== $section->parent_section_id) {
                if ($validated['parent_section_id']) {
                    $newParent = CertificateTemplateSection::where('id', $validated['parent_section_id'])
                        ->where('certificate_template_id', $section->certificate_template_id)
                        ->first();
                    
                    if (!$newParent) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid parent section'
                        ], 400);
                    }

                    if ($newParent->isDescendantOf($section)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot move section: would create circular reference'
                        ], 400);
                    }

                    if (!$newParent->canHaveChildren()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Maximum nesting level exceeded'
                        ], 400);
                    }
                }
            }

            $section->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'section' => $section->fresh(['elements', 'children'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a section
     * 
     * @param CertificateTemplateSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSection(CertificateTemplateSection $section)
    {
        $this->authorize('build', $section->certificateTemplate);
        try {
            $elementCount = $section->elements()->count();
            $childSectionCount = $section->children()->count();
            
            if ($elementCount > 0 || $childSectionCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete section with {$elementCount} elements and {$childSectionCount} subsections. Please remove all content first."
                ], 400);
            }

            $section->delete();

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add an element to a section
     * 
     * @param Request $request
     * @param CertificateTemplateSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function addElement(Request $request, CertificateTemplateSection $section)
    {
        $this->authorize('build', $section->certificateTemplate);
        $validated = $request->validate([
            'element_type' => 'required|in:heading,paragraph,text,strong_text,image,data_field,table,spacer',
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'styling' => 'nullable|array',
            'is_conditional' => 'boolean',
            'conditional_logic' => 'nullable|array'
        ]);

        try {
            $element = $section->elements()->create([
                'element_type' => $validated['element_type'],
                'content' => $validated['content'] ?? '',
                'properties' => $validated['properties'] ?? null,
                'styling' => $validated['styling'] ?? null,
                'is_conditional' => $validated['is_conditional'] ?? false,
                'conditional_logic' => $validated['conditional_logic'] ?? null,
                'sort_order' => CertificateTemplateElement::getNextSortOrder($section->id)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Element added successfully',
                'element' => $element
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an element
     * 
     * @param Request $request
     * @param CertificateTemplateElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateElement(Request $request, CertificateTemplateElement $element)
    {
        $this->authorize('build', $element->section->certificateTemplate);
        $validated = $request->validate([
            'element_type' => 'required|in:heading,paragraph,text,strong_text,image,data_field,table,spacer',
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'styling' => 'nullable|array',
            'is_conditional' => 'boolean',
            'conditional_logic' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $element->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Element updated successfully',
                'element' => $element->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an element
     * 
     * @param CertificateTemplateElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElement(CertificateTemplateElement $element)
    {
        $this->authorize('build', $element->section->certificateTemplate);
        try {
            $element->delete();

            return response()->json([
                'success' => true,
                'message' => 'Element deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder sections
     * 
     * @param Request $request
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderSections(Request $request, CertificateTemplate $certificateTemplate)
    {
        $this->authorize('build', $certificateTemplate);
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'required|integer|exists:certificate_template_sections,id',
            'parent_section_id' => 'nullable|integer|exists:certificate_template_sections,id'
        ]);

        try {
            DB::transaction(function () use ($validated, $certificateTemplate) {
                foreach ($validated['section_ids'] as $index => $sectionId) {
                    CertificateTemplateSection::where('id', $sectionId)
                        ->where('certificate_template_id', $certificateTemplate->id)
                        ->update([
                            'sort_order' => $index + 1,
                            'parent_section_id' => $validated['parent_section_id'] ?? null
                        ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Sections reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder sections: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder elements within a section
     * 
     * @param Request $request
     * @param CertificateTemplateSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderElements(Request $request, CertificateTemplateSection $section)
    {
        $this->authorize('build', $section->certificateTemplate);
        $validated = $request->validate([
            'element_ids' => 'required|array',
            'element_ids.*' => 'required|integer|exists:certificate_template_elements,id'
        ]);

        try {
            DB::transaction(function () use ($validated, $section) {
                foreach ($validated['element_ids'] as $index => $elementId) {
                    CertificateTemplateElement::where('id', $elementId)
                        ->where('certificate_template_section_id', $section->id)
                        ->update(['sort_order' => $index + 1]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Elements reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder elements: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get template structure as JSON for the builder
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTemplateStructure(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('view', $certificateTemplate);
        $certificateTemplate->load([
            'sections.elements' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.children.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return response()->json([
            'success' => true,
            'template' => $certificateTemplate
        ]);
    }

    /**
     * Get available submission form fields for data field elements
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableFields(Request $request)
    {
        try {
            // Get submission forms that are published and active
            $submissionForms = \App\Models\SubmissionForm::published()
                ->active()
                ->with(['sections.elementHolders.elements'])
                ->get();

            $availableFields = [];

            foreach ($submissionForms as $form) {
                $formFields = [];
                
                foreach ($form->sections as $section) {
                    foreach ($section->elementHolders as $holder) {
                        foreach ($holder->elements as $element) {
                            $formFields[] = [
                                'name' => $element->name,
                                'label' => $element->label,
                                'type' => $element->element_type,
                                'section' => $section->title
                            ];
                        }
                    }
                }

                if (!empty($formFields)) {
                    $availableFields[] = [
                        'form_id' => $form->id,
                        'form_name' => $form->name,
                        'fields' => $formFields
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'forms' => $availableFields
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load available fields: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload image for image elements
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $image = $request->file('image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('certificate-templates/images', $filename, 'public');

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'path' => '/storage/' . $path,
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get element type configuration options
     * 
     * @param string $elementType
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElementTypeConfig($elementType)
    {
        $element = new CertificateTemplateElement(['element_type' => $elementType]);
        
        return response()->json([
            'success' => true,
            'config' => [
                'default_properties' => $element->getDefaultProperties(),
                'default_styling' => $element->getDefaultStyling(),
                'supports_rich_text' => $element->supportsRichText(),
                'supports_plain_text' => $element->supportsPlainText(),
                'supports_properties' => $element->supportsProperties(),
                'supports_styling' => $element->supportsStyling(),
                'supports_conditional_logic' => $element->supportsConditionalLogic()
            ]
        ]);
    }

    /**
     * Validate element configuration
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateElement(Request $request)
    {
        $validated = $request->validate([
            'element_type' => 'required|in:heading,paragraph,text,strong_text,image,data_field,table,spacer',
            'properties' => 'nullable|array'
        ]);

        try {
            $element = new CertificateTemplateElement([
                'element_type' => $validated['element_type'],
                'properties' => $validated['properties'] ?? []
            ]);

            $errors = $element->validateProperties();

            return response()->json([
                'success' => empty($errors),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => ['Validation failed: ' . $e->getMessage()]
            ], 500);
        }
    }
}