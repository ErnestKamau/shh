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
use Illuminate\Support\Facades\Log;
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
            'section_type' => 'required|in:regular,rows_section',
            'section_alignment' => 'required|in:left,middle,right',
            'section_logos' => 'nullable|array',
            'section_logos.*' => 'file|image|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'section_logo_positions' => 'nullable|array',
            'section_logo_positions.*' => 'nullable|in:left,middle,right',
        ]);

        try {
            $section = $submissionForm->sections()->create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'section_type' => $validated['section_type'],
                'section_alignment' => $validated['section_alignment'],
                'section_logos' => $this->storeSectionLogos($request, 'section_logos', 'section_logo_positions'),
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
            'section_type' => 'required|in:regular,rows_section',
            'section_alignment' => 'required|in:left,middle,right',
            'existing_section_logos' => 'nullable|array',
            'existing_section_logos.*' => 'string|max:255',
            'existing_section_logo_positions' => 'nullable|array',
            'existing_section_logo_positions.*' => 'nullable|in:left,middle,right',
            'section_logos' => 'nullable|array',
            'section_logos.*' => 'file|image|mimes:jpg,jpeg,png,gif,webp,svg|max:5120',
            'section_logo_positions' => 'nullable|array',
            'section_logo_positions.*' => 'nullable|in:left,middle,right',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        try {
            $existingLogoPaths = array_values(array_filter($validated['existing_section_logos'] ?? [], function ($path) {
                return is_string($path) && trim($path) !== '';
            }));

            $existingLogoPositions = $validated['existing_section_logo_positions'] ?? [];
            $existingLogos = [];
            foreach ($existingLogoPaths as $index => $path) {
                $existingLogos[] = [
                    'path' => $path,
                    'position' => $existingLogoPositions[$index] ?? 'left',
                ];
            }

            $newLogos = $this->storeSectionLogos($request, 'section_logos', 'section_logo_positions');

            $section->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'section_type' => $validated['section_type'],
                'section_alignment' => $validated['section_alignment'],
                'sort_order' => $validated['sort_order'] ?? $section->sort_order,
                'section_logos' => array_values(array_merge($existingLogos, $newLogos)),
            ]);

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
     * Persist uploaded section logos and return their storage paths.
     *
     * @return array<int, string>
     */
    private function storeSectionLogos(Request $request, string $fileKey, string $positionKey): array
    {
        if (!$request->hasFile($fileKey)) {
            return [];
        }

        $uploadedFiles = $request->file($fileKey);
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $positions = $request->input($positionKey, []);
        $storedPaths = [];

        foreach ($uploadedFiles as $index => $logo) {
            if (!$logo) {
                continue;
            }

            $storedPaths[] = [
                'path' => $logo->store('submission-form-sections/logos', 'public'),
                'position' => $positions[$index] ?? 'left',
            ];
        }

        return $storedPaths;
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
            DB::transaction(function () use ($section) {
                $holders = $section->elementHolders()->with('elements.instanceValues')->get();

                foreach ($holders as $holder) {
                    foreach ($holder->elements as $element) {
                        if ($element->instanceValues->isNotEmpty()) {
                            throw new \RuntimeException('This section contains form elements with submitted data and cannot be deleted.');
                        }
                    }
                }

                foreach ($holders as $holder) {
                    $holder->elements()->delete();
                }

                $section->elementHolders()->delete();
                $section->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get an element holder
     * 
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function getElementHolder(SubmissionFormElementHolder $holder)
    {
        try {
            return response()->json([
                'success' => true,
                'holder' => $holder
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load element holder: ' . $e->getMessage()
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
            DB::transaction(function () use ($holder) {
                $elements = $holder->elements()->with('instanceValues')->get();

                foreach ($elements as $element) {
                    if ($element->instanceValues->isNotEmpty()) {
                        throw new \RuntimeException('This element holder contains form elements with submitted data and cannot be deleted.');
                    }
                }

                $holder->elements()->delete();
                $holder->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Element holder deleted successfully'
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
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
        // Debug: Log the incoming request data
        Log::info('AddElement Request Data:', $request->all());
        
        $validated = $request->validate([
            'element_type' => 'required|in:text,number,email,date,datetime,textarea,plain_text,select,radio,checkbox,file,signature,contact_signature,calculation,client_select,sample_type_select,client_unit_select,client_contact_select,client_submission_officers_select,analysis_type_select,analysis_elements_select,store_select,store_slot_select,sample_condition_select,standard_select,sample_point_select,company_sub_unit_select,user_select,user_signature,depended_field',
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
            'is_required' => 'sometimes|in:true,false,1,0',
            'is_readonly' => 'sometimes|in:true,false,1,0',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'calculation_formula' => 'nullable|string|max:1000',
            'conditional_logic' => 'nullable|array',
            'mapping_table' => 'nullable|in:sample_headers,sample_details',
            'mapping_field' => 'nullable|string|max:255',
            'is_mapped' => 'sometimes|in:true,false,1,0',
            'depends_on_type' => 'nullable|string|max:100',
            'depends_on_field' => 'nullable|string|max:255',
            'source_table' => 'nullable|string|max:255',
            'source_field' => 'nullable|string|max:255',
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
                'is_required' => filter_var($validated['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_readonly' => filter_var($validated['is_readonly'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'default_value' => $validated['default_value'] ?? null,
                'validation_rules' => $validated['validation_rules'] ?? null,
                'options' => $validated['options'] ?? null,
                'calculation_formula' => $validated['calculation_formula'] ?? null,
                'conditional_logic' => $validated['conditional_logic'] ?? null,
                'mapping_table' => $validated['mapping_table'] ?? null,
                'mapping_field' => $validated['mapping_field'] ?? null,
                'is_mapped' => filter_var($validated['is_mapped'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'depends_on_type' => $validated['depends_on_type'] ?? null,
                'depends_on_field' => $validated['depends_on_field'] ?? null,
                'source_table' => $validated['source_table'] ?? null,
                'source_field' => $validated['source_field'] ?? null,
                'sort_order' => SubmissionFormElement::getNextSortOrder($holder->id)
            ]);
            
            // Debug: Log the created element data
            Log::info('Created Element Data:', [
                'id' => $element->id,
                'mapping_table' => $element->mapping_table,
                'mapping_field' => $element->mapping_field,
                'is_mapped' => $element->is_mapped
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
        // Debug: Log the incoming request data
        Log::info('UpdateElement Request Data:', $request->all());
        
        $validated = $request->validate([
            'element_type' => 'required|in:text,number,email,date,datetime,textarea,plain_text,select,radio,checkbox,file,signature,contact_signature,calculation,client_select,sample_type_select,client_unit_select,client_contact_select,client_submission_officers_select,analysis_type_select,analysis_elements_select,store_select,store_slot_select,sample_condition_select,standard_select,sample_point_select,company_sub_unit_select,user_select,user_signature,depended_field',
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
            'is_required' => 'sometimes|in:true,false,1,0',
            'is_readonly' => 'sometimes|in:true,false,1,0',
            'default_value' => 'nullable|string',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'calculation_formula' => 'nullable|string|max:1000',
            'conditional_logic' => 'nullable|array',
            'mapping_table' => 'nullable|in:sample_headers,sample_details',
            'mapping_field' => 'nullable|string|max:255',
            'is_mapped' => 'sometimes|in:true,false,1,0',
            'sort_order' => 'nullable|integer|min:0',
            'depends_on_type' => 'nullable|string|max:100',
            'depends_on_field' => 'nullable|string|max:255',
            'source_table' => 'nullable|string|max:255',
            'source_field' => 'nullable|string|max:255',
        ]);

        try {
            // Convert string boolean values to actual booleans
            if (isset($validated['is_required'])) {
                $validated['is_required'] = filter_var($validated['is_required'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($validated['is_readonly'])) {
                $validated['is_readonly'] = filter_var($validated['is_readonly'], FILTER_VALIDATE_BOOLEAN);
            }
            if (isset($validated['is_mapped'])) {
                $validated['is_mapped'] = filter_var($validated['is_mapped'], FILTER_VALIDATE_BOOLEAN);
            }
            
            $element->update($validated);
            
            // Debug: Log the updated element data
            Log::info('Updated Element Data:', [
                'id' => $element->id,
                'mapping_table' => $element->mapping_table,
                'mapping_field' => $element->mapping_field,
                'is_mapped' => $element->is_mapped
            ]);

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

    /**
     * Get mapping fields for a specific table
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMappingFields(Request $request)
    {
        $table = $request->get('table');
        
        if (!$table || !in_array($table, ['sample_headers', 'sample_details'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid table specified'
            ], 400);
        }

        $fields = SubmissionFormElement::getMappingFields($table);

        return response()->json([
            'success' => true,
            'fields' => $fields
        ]);
    }

    /**
     * Clone a section with all its element holders and elements
     * 
     * @param Request $request
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneSection(Request $request, SubmissionFormSection $section)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();
            
            $clonedSection = $section->clone($validated['title'] ?? null);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Section cloned successfully',
                'section' => $clonedSection
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to clone section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone an element holder with all its elements
     * 
     * @param Request $request
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneElementHolder(Request $request, SubmissionFormElementHolder $holder)
    {
        try {
            DB::beginTransaction();
            
            $clonedHolder = $holder->clone();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Element holder cloned successfully',
                'holder' => $clonedHolder
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to clone element holder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone a form element
     * 
     * @param Request $request
     * @param SubmissionFormElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneElement(Request $request, SubmissionFormElement $element)
    {
        try {
            DB::beginTransaction();
            
            $clonedElement = $element->clone();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form element cloned successfully',
                'element' => $clonedElement
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to clone form element: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a section to a specific position
     * 
     * @param Request $request
     * @param SubmissionFormSection $section
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveSectionToPosition(Request $request, SubmissionFormSection $section)
    {
        $validated = $request->validate([
            'position' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();
            
            $form = $section->submissionForm;
            $totalSections = $form->sections()->count();
            $newPosition = min($validated['position'], $totalSections);
            
            // Get all sections ordered by sort_order
            $sections = $form->sections()->orderBy('sort_order')->get();
            
            // Remove the section from its current position
            $sections = $sections->filter(function($s) use ($section) {
                return $s->id !== $section->id;
            })->values();
            
            // Insert the section at the new position
            $sections->splice($newPosition - 1, 0, [$section]);
            
            // Update sort orders
            foreach ($sections as $index => $s) {
                $s->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Section moved successfully',
                'new_position' => $newPosition
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to move section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move an element holder to a different section
     * 
     * @param Request $request
     * @param SubmissionFormElementHolder $holder
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveHolderToSection(Request $request, SubmissionFormElementHolder $holder)
    {
        $validated = $request->validate([
            'target_section_id' => 'required|integer|exists:submission_form_sections,id',
            'position' => 'nullable|integer|min:1'
        ]);

        try {
            DB::beginTransaction();
            
            $targetSection = SubmissionFormSection::findOrFail($validated['target_section_id']);
            
            // Check if target section is different
            if ($holder->submission_form_section_id === $targetSection->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Element holder is already in this section'
                ], 400);
            }
            
            // Update the holder's section
            $holder->update(['submission_form_section_id' => $targetSection->id]);
            
            // Reorder holders in the target section
            $holders = $targetSection->elementHolders()->orderBy('sort_order')->get();
            $position = $validated['position'] ?? $holders->count();
            
            // Remove the moved holder from the list
            $holders = $holders->filter(function($h) use ($holder) {
                return $h->id !== $holder->id;
            })->values();
            
            // Insert at the specified position
            $holders->splice($position - 1, 0, [$holder]);
            
            // Update sort orders
            foreach ($holders as $index => $h) {
                $h->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Element holder moved successfully',
                'new_section_id' => $targetSection->id,
                'new_position' => $position
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to move element holder: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a form element to a different holder
     * 
     * @param Request $request
     * @param SubmissionFormElement $element
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveElementToHolder(Request $request, SubmissionFormElement $element)
    {
        $validated = $request->validate([
            'target_holder_id' => 'required|integer|exists:submission_form_element_holders,id',
            'position' => 'nullable|integer|min:1'
        ]);

        try {
            DB::beginTransaction();
            
            $targetHolder = SubmissionFormElementHolder::findOrFail($validated['target_holder_id']);
            
            // Check if target holder is different
            if ($element->submission_form_element_holder_id === $targetHolder->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Element is already in this holder'
                ], 400);
            }
            
            // Check if target holder has capacity
            if ($targetHolder->isAtCapacity()) {
                return response()->json([
                    'success' => false,
                    'message' => "Target holder is at maximum capacity ({$targetHolder->max_elements} elements)"
                ], 400);
            }
            
            // Update the element's holder
            $element->update(['submission_form_element_holder_id' => $targetHolder->id]);
            
            // Reorder elements in the target holder
            $elements = $targetHolder->elements()->orderBy('sort_order')->get();
            $position = $validated['position'] ?? $elements->count();
            
            // Remove the moved element from the list
            $elements = $elements->filter(function($e) use ($element) {
                return $e->id !== $element->id;
            })->values();
            
            // Insert at the specified position
            $elements->splice($position - 1, 0, [$element]);
            
            // Update sort orders
            foreach ($elements as $index => $e) {
                $e->update(['sort_order' => $index + 1]);
            }
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Form element moved successfully',
                'new_holder_id' => $targetHolder->id,
                'new_position' => $position
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to move form element: ' . $e->getMessage()
            ], 500);
        }
    }
}