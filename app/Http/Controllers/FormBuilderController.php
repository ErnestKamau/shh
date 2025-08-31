<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormSection;
use App\Models\SubmissionFormElementHolder;
use App\Models\SubmissionFormElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FormBuilderController extends Controller
{
    /**
     * Display the form builder interface
     */
    public function index(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('submission-forms.builder', compact('submissionForm'));
    }

    /**
     * Add a new section to the form
     * 
     * @param Request $request
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\JsonResponse
     */
    public function addSection(Request $request, SubmissionForm $submissionForm)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $section = $submissionForm->sections()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'sort_order' => SubmissionFormSection::getNextSortOrder($submissionForm->id)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Section added successfully',
                'section' => $section->load('elementHolders.elements')
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
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSection(Request $request, SubmissionFormSection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $section->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully',
                'section' => $section->fresh()
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
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSection(SubmissionFormSection $section)
    {
        try {
            $elementCount = $section->elementHolders()->withCount('elements')->get()->sum('elements_count');
            
            if ($elementCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot delete section with {$elementCount} form elements. Please remove all elements first."
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
     * Add an element holder to a section
     * 
     * @param Request $request
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function addElementHolder(Request $request, SubmissionFormSection $section)
    {
        $validated = $request->validate([
            'holder_type' => 'required|in:field,text',
            'max_elements' => 'required|integer|min:1|max:20',
        ]);

        try {
            $holder = $section->elementHolders()->create([
                'holder_type' => $validated['holder_type'],
                'max_elements' => $validated['max_elements'],
                'sort_order' => SubmissionFormElementHolder::getNextSortOrder($section->id)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Element holder added successfully',
                'holder' => $holder->load('elements')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add element holder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an element holder
     * 
     * @param Request $request
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateElementHolder(Request $request, SubmissionFormElementHolder $holder)
    {
        $validated = $request->validate([
            'holder_type' => 'required|in:field,text',
            'max_elements' => 'required|integer|min:1|max:20',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            // Check if reducing max_elements would exceed current element count
            if ($validated['max_elements'] < $holder->getCurrentElementCount()) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot reduce max elements below current element count ({$holder->getCurrentElementCount()})"
                ], 400);
            }

            $holder->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Element holder updated successfully',
                'holder' => $holder->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update element holder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an element holder
     * 
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElementHolder(SubmissionFormElementHolder $holder)
    {
        try {
            if ($holder->elements()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete element holder that contains form elements. Please remove all elements first.'
                ], 400);
            }

            $holder->delete();

            return response()->json([
                'success' => true,
                'message' => 'Element holder deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete element holder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a form element to a holder
     * 
     * @param Request $request
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function addElement(Request $request, SubmissionFormElementHolder $holder)
    {
        $validated = $request->validate([
            'element_type' => 'required|in:text,number,email,date,datetime,textarea,select,radio,checkbox,file,signature,calculation',
            'label' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                function ($attribute, $value, $fail) use ($holder) {
                    $exists = SubmissionFormElement::whereHas('holder.section', function ($query) use ($holder) {
                        $query->where('submission_form_id', $holder->section->submission_form_id);
                    })->where('name', $value)->exists();
                    
                    if ($exists) {
                        $fail('The name has already been taken within this form.');
                    }
                }
            ],
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:1000',
            'is_required' => 'boolean',
            'is_readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'calculation_formula' => 'nullable|string|max:1000',
            'conditional_logic' => 'nullable|array'
        ]);

        try {
            // Check if holder is at capacity
            if (!$holder->canAddMoreElements()) {
                return response()->json([
                    'success' => false,
                    'message' => "Element holder is at maximum capacity ({$holder->max_elements} elements)"
                ], 400);
            }

            $element = $holder->elements()->create([
                'element_type' => $validated['element_type'],
                'label' => $validated['label'],
                'name' => $validated['name'],
                'placeholder' => $validated['placeholder'] ?? null,
                'help_text' => $validated['help_text'] ?? null,
                'is_required' => $validated['is_required'] ?? false,
                'is_readonly' => $validated['is_readonly'] ?? false,
                'default_value' => $validated['default_value'] ?? null,
                'validation_rules' => $validated['validation_rules'] ?? null,
                'options' => $validated['options'] ?? null,
                'calculation_formula' => $validated['calculation_formula'] ?? null,
                'conditional_logic' => $validated['conditional_logic'] ?? null,
                'sort_order' => SubmissionFormElement::getNextSortOrder($holder->id)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Form element added successfully',
                'element' => $element
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add form element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a form element
     * 
     * @param Request $request
     * @param SubmissionFormElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateElement(Request $request, SubmissionFormElement $element)
    {
        $validated = $request->validate([
            'element_type' => 'required|in:text,number,email,date,datetime,textarea,select,radio,checkbox,file,signature,calculation',
            'label' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z][a-zA-Z0-9_]*$/',
                function ($attribute, $value, $fail) use ($element) {
                    $exists = SubmissionFormElement::whereHas('holder.section', function ($query) use ($element) {
                        $query->where('submission_form_id', $element->holder->section->submission_form_id);
                    })->where('name', $value)->where('id', '!=', $element->id)->exists();
                    
                    if ($exists) {
                        $fail('The name has already been taken within this form.');
                    }
                }
            ],
            'placeholder' => 'nullable|string|max:255',
            'help_text' => 'nullable|string|max:1000',
            'is_required' => 'boolean',
            'is_readonly' => 'boolean',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'calculation_formula' => 'nullable|string|max:1000',
            'conditional_logic' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $element->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Form element updated successfully',
                'element' => $element->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update form element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a form element
     * 
     * @param SubmissionFormElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteElement(SubmissionFormElement $element)
    {
        try {
            // Check if element has instance values
            if ($element->instanceValues()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete form element that has submitted data. Consider making it readonly instead.'
                ], 400);
            }

            $element->delete();

            return response()->json([
                'success' => true,
                'message' => 'Form element deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete form element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder sections
     * 
     * @param Request $request
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderSections(Request $request, SubmissionForm $submissionForm)
    {
        $validated = $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'required|integer|exists:submission_form_sections,id'
        ]);

        try {
            DB::transaction(function () use ($validated, $submissionForm) {
                foreach ($validated['section_ids'] as $index => $sectionId) {
                    SubmissionFormSection::where('id', $sectionId)
                        ->where('submission_form_id', $submissionForm->id)
                        ->update(['sort_order' => $index + 1]);
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
     * Reorder element holders within a section
     * 
     * @param Request $request
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderElementHolders(Request $request, SubmissionFormSection $section)
    {
        $validated = $request->validate([
            'holder_ids' => 'required|array',
            'holder_ids.*' => 'required|integer|exists:submission_form_element_holders,id'
        ]);

        try {
            DB::transaction(function () use ($validated, $section) {
                foreach ($validated['holder_ids'] as $index => $holderId) {
                    SubmissionFormElementHolder::where('id', $holderId)
                        ->where('submission_form_section_id', $section->id)
                        ->update(['sort_order' => $index + 1]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Element holders reordered successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder element holders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder elements within a holder
     * 
     * @param Request $request
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderElements(Request $request, SubmissionFormElementHolder $holder)
    {
        $validated = $request->validate([
            'element_ids' => 'required|array',
            'element_ids.*' => 'required|integer|exists:submission_form_elements,id'
        ]);

        try {
            DB::transaction(function () use ($validated, $holder) {
                foreach ($validated['element_ids'] as $index => $elementId) {
                    SubmissionFormElement::where('id', $elementId)
                        ->where('submission_form_element_holder_id', $holder->id)
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
     * Get form structure as JSON for the builder
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFormStructure(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return response()->json([
            'success' => true,
            'form' => $submissionForm
        ]);
    }

    /**
     * Validate element name uniqueness within form
     * 
     * @param Request $request
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateElementName(Request $request, SubmissionForm $submissionForm)
    {
        $name = $request->get('name');
        $excludeId = $request->get('exclude_id');

        $exists = SubmissionFormElement::whereHas('holder', function ($holderQuery) use ($submissionForm) {
            $holderQuery->whereHas('section', function ($sectionQuery) use ($submissionForm) {
                $sectionQuery->where('submission_form_id', $submissionForm->id);
            });
        })
        ->where('name', $name)
        ->when($excludeId, function ($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId);
        })
        ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Element name already exists in this form' : 'Element name is available'
        ]);
    }
}