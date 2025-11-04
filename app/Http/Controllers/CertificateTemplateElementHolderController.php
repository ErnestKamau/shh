<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplateElementHolder;
use App\CertificateTemplateSection;
use App\Services\CertificateTemplateDataSourceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CertificateTemplateElementHolderController extends Controller
{
    /**
     * Get available fields for a data source.
     */
    public function getFieldsForDataSource(Request $request): JsonResponse
    {
        $request->validate([
            'data_source' => 'required|in:Company,CRMCustomer,SampleHeader,SampleDetails,CapturedResult',
        ]);

        $fields = CertificateTemplateDataSourceService::getFieldsForDataSource($request->data_source);

        return response()->json([
            'success' => true,
            'fields' => $fields,
        ]);
    }

    /**
     * Store a newly created element holder.
     */
    public function store(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $request->validate([
            'holder_type' => 'required|in:field,text,company_header',
            'parent_holder_id' => 'nullable|exists:certificate_template_element_holders,id',
            'direction' => 'nullable|in:horizontal,vertical',
            'data_source' => 'nullable|in:Company,CRMCustomer,SampleHeader,SampleDetails,CapturedResult',
            'field_mappings' => 'nullable|array',
            'field_mappings.*' => 'nullable|string',
            'max_elements' => 'nullable|integer|min:0|max:1000',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'flex_grow' => 'nullable|numeric',
            'flex_shrink' => 'nullable|numeric',
            'flex_basis' => 'nullable|string|max:50',
            // Company Information (deprecated - use data_source and field_mappings instead)
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_logo' => 'nullable|string|max:500',
            // Document QA Details (deprecated)
            'form_number' => 'nullable|string|max:255',
            'publish_date' => 'nullable|date',
            'qa_other_details' => 'nullable|string',
        ]);

        // Check capacity if creating a nested holder
        if ($request->parent_holder_id) {
            $parentHolder = CertificateTemplateElementHolder::find($request->parent_holder_id);
            
            // Validate parent holder has capacity (each nested holder counts as 1 slot)
            if (!$parentHolder->hasCapacity()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parent holder has reached maximum capacity. Current: ' . 
                                 ($parentHolder->elements()->count() + $parentHolder->childHolders()->count()) . 
                                 '/' . ($parentHolder->max_elements ?? '∞'),
                ], 422);
            }
            
            $maxSortOrder = $parentHolder->childHolders()->max('sort_order') ?? 0;
        } else {
            $maxSortOrder = $section->elementHolders()->whereNull('parent_holder_id')->max('sort_order') ?? 0;
        }

        $holderData = [
            'certificate_template_section_id' => $section->id,
            'parent_holder_id' => $request->parent_holder_id,
            'holder_type' => $request->holder_type,
            'direction' => $request->direction ?? 'horizontal',
            'data_source' => $request->data_source,
            'field_mappings' => $request->field_mappings,
            'max_elements' => $request->max_elements ?? 0, // 0 means unlimited
            'sort_order' => $maxSortOrder + 1,
            'position_x' => $request->position_x ?? 50,
            'position_y' => $request->position_y ?? 50,
            'width' => $request->width ?? 300,
            'height' => $request->height ?? 200,
            'flex_grow' => $request->flex_grow ?? 0,
            'flex_shrink' => $request->flex_shrink ?? 1,
            'flex_basis' => $request->flex_basis,
        ];
        
        // Add company information if holder type is company_header (backward compatibility)
        if ($request->holder_type === 'company_header' && !$request->data_source) {
            $holderData['company_name'] = $request->company_name;
            $holderData['company_email'] = $request->company_email;
            $holderData['company_website'] = $request->company_website;
            $holderData['company_phone'] = $request->company_phone;
            $holderData['company_logo'] = $request->company_logo;
            $holderData['form_number'] = $request->form_number;
            $holderData['publish_date'] = $request->publish_date;
            $holderData['qa_other_details'] = $request->qa_other_details;
        }
        
        $holder = CertificateTemplateElementHolder::create($holderData);

        // Calculate percentages based on canvas size
        if ($request->has('canvas_width') && $request->has('canvas_height')) {
            $holder->update([
                'position_x_percent' => ($holder->position_x / $request->canvas_width) * 100,
                'position_y_percent' => ($holder->position_y / $request->canvas_height) * 100,
                'width_percent' => ($holder->width / $request->canvas_width) * 100,
                'height_percent' => ($holder->height / $request->canvas_height) * 100,
            ]);
        }

        // Auto-create elements from selected field mappings
        if ($request->has('field_mappings') && is_array($request->field_mappings) && !empty($request->field_mappings)) {
            $fieldMappings = $request->field_mappings;
            $dataSource = $request->data_source;
            $canvasWidth = $request->canvas_width ?? 794;
            $canvasHeight = $request->canvas_height ?? 1123;
            
            // Calculate element dimensions based on holder direction and number of fields
            $holderDirection = $request->direction ?? 'horizontal';
            $fieldCount = count($fieldMappings);
            $holderWidth = $holder->width ?? 300;
            $holderHeight = $holder->height ?? 200;
            
            // Calculate element size based on direction and number of fields
            if ($holderDirection === 'horizontal') {
                // Horizontal: elements should fit side by side
                $elementWidth = max(100, ($holderWidth - 20 - (($fieldCount - 1) * 8)) / $fieldCount);
                $elementHeight = max(30, $holderHeight - 60); // Account for header
            } else {
                // Vertical: elements stack, each takes full width
                $elementWidth = max(100, $holderWidth - 20);
                $elementHeight = max(30, ($holderHeight - 60 - (($fieldCount - 1) * 8)) / $fieldCount);
            }
            
            $sortOrder = 0;
            $positionOffset = 10;
            
            foreach ($fieldMappings as $fieldName => $fieldValue) {
                // Skip if fieldName is empty or not a valid string key
                if (empty($fieldName) || !is_string($fieldName)) {
                    continue;
                }
                
                // Get field label from service
                $fieldLabel = \App\Services\CertificateTemplateDataSourceService::getFieldLabel($dataSource, $fieldName);
                
                // Calculate position based on direction
                if ($holderDirection === 'horizontal') {
                    $positionX = $positionOffset + ($sortOrder * ($elementWidth + 8));
                    $positionY = 50; // Below holder header
                } else {
                    $positionX = 10;
                    $positionY = $positionOffset + ($sortOrder * ($elementHeight + 8));
                }
                
                // Create element for this field
                // Store field mapping in properties array
                \App\CertificateTemplateElement::create([
                    'certificate_template_section_id' => $section->id,
                    'certificate_template_element_holder_id' => $holder->id,
                    'element_type' => 'data_field',
                    'content' => $fieldName, // Store the field name for data binding
                    'properties' => [
                        'field_name' => $fieldName,
                        'field_label' => $fieldLabel,
                        'data_source' => $dataSource,
                    ],
                    'sort_order' => ++$sortOrder,
                    'position_x' => $positionX,
                    'position_y' => $positionY,
                    'width' => $elementWidth,
                    'height' => $elementHeight,
                    'position_x_percent' => ($positionX / $canvasWidth) * 100,
                    'position_y_percent' => ($positionY / $canvasHeight) * 100,
                    'width_percent' => ($elementWidth / $canvasWidth) * 100,
                    'height_percent' => ($elementHeight / $canvasHeight) * 100,
                    'z_index' => 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Element holder created successfully' . 
                        (isset($fieldMappings) && count($fieldMappings) > 0 
                            ? ' with ' . count($fieldMappings) . ' element(s).' 
                            : '.'),
            'holder' => $holder->fresh()->load(['elements', 'childHolders', 'parentHolder'])
        ]);
    }

    /**
     * Display the specified element holder.
     */
    public function show(CertificateTemplateElementHolder $holder): JsonResponse
    {
        return response()->json([
            'success' => true,
            'holder' => $holder->load(['elements', 'section', 'childHolders', 'parentHolder'])
        ]);
    }

    /**
     * Update the specified element holder.
     */
    public function update(Request $request, CertificateTemplateElementHolder $holder): JsonResponse
    {
        $request->validate([
            'holder_type' => 'sometimes|in:field,text,company_header',
            'parent_holder_id' => 'nullable|exists:certificate_template_element_holders,id',
            'direction' => 'nullable|in:horizontal,vertical',
            'data_source' => 'nullable|in:Company,CRMCustomer,SampleHeader,SampleDetails,CapturedResult',
            'field_mappings' => 'nullable|array',
            'field_mappings.*' => 'nullable|string',
            'max_elements' => 'nullable|integer|min:0|max:1000',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'flex_grow' => 'nullable|numeric',
            'flex_shrink' => 'nullable|numeric',
            'flex_basis' => 'nullable|string|max:50',
            // Company Information (deprecated)
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|url|max:255',
            'company_phone' => 'nullable|string|max:50',
            'company_logo' => 'nullable|string|max:500',
            // Document QA Details (deprecated)
            'form_number' => 'nullable|string|max:255',
            'publish_date' => 'nullable|date',
            'qa_other_details' => 'nullable|string',
        ]);

        $updateData = $request->only([
            'holder_type',
            'parent_holder_id',
            'direction',
            'data_source',
            'field_mappings',
            'max_elements',
            'position_x',
            'position_y',
            'width',
            'height',
            'position_x_percent',
            'position_y_percent',
            'width_percent',
            'height_percent',
            'flex_grow',
            'flex_shrink',
            'flex_basis',
        ]);
        
        // Include company information fields (backward compatibility)
        if ($request->has('company_name')) {
            $updateData = array_merge($updateData, $request->only([
                'company_name',
                'company_email',
                'company_website',
                'company_phone',
                'company_logo',
                'form_number',
                'publish_date',
                'qa_other_details',
            ]));
        }
        
        $holder->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Element holder updated successfully.',
            'holder' => $holder->fresh()->load(['elements', 'childHolders', 'parentHolder'])
        ]);
    }

    /**
     * Remove the specified element holder.
     */
    public function destroy(CertificateTemplateElementHolder $holder): JsonResponse
    {
        $holder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Element holder deleted successfully.'
        ]);
    }

    /**
     * Reorder element holders within a section.
     */
    public function reorder(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $request->validate([
            'holder_ids' => 'required|array',
            'holder_ids.*' => 'required|integer|exists:certificate_template_element_holders,id'
        ]);

        DB::transaction(function () use ($request, $section) {
            foreach ($request->holder_ids as $index => $holderId) {
                CertificateTemplateElementHolder::where('id', $holderId)
                    ->where('certificate_template_section_id', $section->id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Element holders reordered successfully.'
        ]);
    }

    /**
     * Clone an element holder with all its elements and nested holders.
     */
    public function clone(CertificateTemplateElementHolder $holder): JsonResponse
    {
        DB::transaction(function () use ($holder, &$newHolder) {
            // Clone the holder
            $newHolder = $holder->replicate();
            $newHolder->sort_order = $holder->parent_holder_id 
                ? $holder->parentHolder->childHolders()->max('sort_order') + 1
                : $holder->section->elementHolders()->whereNull('parent_holder_id')->max('sort_order') + 1;
            $newHolder->save();

            // Clone all elements
            foreach ($holder->elements as $element) {
                $newElement = $element->replicate();
                $newElement->certificate_template_element_holder_id = $newHolder->id;
                $newElement->save();
            }

            // Clone all nested holders recursively
            foreach ($holder->childHolders as $childHolder) {
                $this->cloneHolderRecursively($childHolder, $newHolder);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Element holder cloned successfully.',
            'holder' => $newHolder->load(['elements', 'childHolders'])
        ]);
    }

    /**
     * Helper method to clone holders recursively.
     */
    private function cloneHolderRecursively(CertificateTemplateElementHolder $holder, CertificateTemplateElementHolder $newParent): void
    {
        $newHolder = $holder->replicate();
        $newHolder->parent_holder_id = $newParent->id;
        $newHolder->sort_order = $newParent->childHolders()->max('sort_order') + 1;
        $newHolder->save();

        // Clone elements
        foreach ($holder->elements as $element) {
            $newElement = $element->replicate();
            $newElement->certificate_template_element_holder_id = $newHolder->id;
            $newElement->save();
        }

        // Clone nested holders
        foreach ($holder->childHolders as $childHolder) {
            $this->cloneHolderRecursively($childHolder, $newHolder);
        }
    }

    /**
     * Store a nested holder inside a parent holder.
     */
    public function storeNestedHolder(Request $request, CertificateTemplateElementHolder $parentHolder): JsonResponse
    {
        $section = $parentHolder->section;
        $request->merge([
            'section_id' => $section->id,
            'parent_holder_id' => $parentHolder->id,
        ]);

        return $this->store($request, $section);
    }

    /**
     * Update holder position and size.
     */
    public function updatePosition(Request $request, CertificateTemplateElementHolder $holder): JsonResponse
    {
        $request->validate([
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'position_x_percent' => 'nullable|numeric',
            'position_y_percent' => 'nullable|numeric',
            'width_percent' => 'nullable|numeric',
            'height_percent' => 'nullable|numeric',
            'direction' => 'nullable|in:horizontal,vertical',
            'flex_grow' => 'nullable|numeric',
            'flex_shrink' => 'nullable|numeric',
            'flex_basis' => 'nullable|string|max:50',
        ]);

        $holder->update($request->only([
            'position_x',
            'position_y',
            'width',
            'height',
            'position_x_percent',
            'position_y_percent',
            'width_percent',
            'height_percent',
            'direction',
            'flex_grow',
            'flex_shrink',
            'flex_basis',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Holder position updated successfully.',
            'holder' => $holder->fresh()->load(['elements', 'childHolders'])
        ]);
    }

    /**
     * Toggle holder direction.
     */
    public function toggleDirection(CertificateTemplateElementHolder $holder): JsonResponse
    {
        $holder->direction = $holder->direction === 'horizontal' ? 'vertical' : 'horizontal';
        $holder->save();

        return response()->json([
            'success' => true,
            'message' => 'Holder direction updated successfully.',
            'holder' => $holder->fresh()->load(['elements', 'childHolders'])
        ]);
    }
}
