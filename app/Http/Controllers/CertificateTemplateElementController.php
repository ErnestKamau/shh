<?php

namespace App\Http\Controllers;

use App\CertificateTemplateElement;
use App\Models\CertificateTemplateElementHolder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CertificateTemplateElementController extends Controller
{
    /**
     * Display the specified element.
     */
    public function show(CertificateTemplateElement $element): JsonResponse
    {
        return response()->json([
            'success' => true,
            'element' => $element->load(['section', 'elementHolder'])
        ]);
    }

    /**
     * Store a newly created element.
     */
    public function store(Request $request, CertificateTemplateElementHolder $holder): JsonResponse
    {
        // Check holder capacity
        if (!$holder->hasCapacity()) {
            return response()->json([
                'success' => false,
                'message' => "This holder is at maximum capacity ({$holder->max_elements} elements)."
            ], 422);
        }

        $request->validate([
            'element_type' => 'required|string',
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        // Get the highest sort order for this holder
        $maxSortOrder = $holder->elements()->max('sort_order') ?? 0;

        $element = CertificateTemplateElement::create([
            'certificate_template_section_id' => $holder->section->id,
            'certificate_template_element_holder_id' => $holder->id,
            'element_type' => $request->element_type,
            'content' => $request->content ?? $this->getDefaultContent($request->element_type),
            'properties' => $request->properties ?? [],
            'sort_order' => $maxSortOrder + 1,
            'position_x' => $request->position_x ?? 10,
            'position_y' => $request->position_y ?? 10,
            'width' => $request->width ?? 200,
            'height' => $request->height ?? 100,
            'z_index' => $request->z_index ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Element created successfully.',
            'element' => $element
        ]);
    }

    /**
     * Update the specified element.
     */
    public function update(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $request->validate([
            'content' => 'nullable|string',
            'properties' => 'nullable|array',
            'element_type' => 'sometimes|string',
        ]);

        $element->update($request->only([
            'content',
            'properties',
            'element_type',
            'styling',
            'is_conditional',
            'conditional_logic'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Element updated successfully.',
            'element' => $element->fresh()
        ]);
    }

    /**
     * Update element position and size.
     */
    public function updatePosition(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $request->validate([
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
            'position_x_percent' => 'nullable|numeric',
            'position_y_percent' => 'nullable|numeric',
            'width_percent' => 'nullable|numeric',
            'height_percent' => 'nullable|numeric',
            'z_index' => 'nullable|integer',
        ]);

        $element->update($request->only([
            'position_x',
            'position_y',
            'width',
            'height',
            'position_x_percent',
            'position_y_percent',
            'width_percent',
            'height_percent',
            'z_index'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Element position updated successfully.',
            'element' => $element
        ]);
    }

    /**
     * Remove the specified element.
     */
    public function destroy(CertificateTemplateElement $element): JsonResponse
    {
        $element->delete();

        return response()->json([
            'success' => true,
            'message' => 'Element deleted successfully.'
        ]);
    }

    /**
     * Get default content for element type.
     */
    private function getDefaultContent(string $elementType): string
    {
        return match($elementType) {
            'heading' => 'New Heading',
            'paragraph' => 'Enter your text here...',
            'data_field' => 'sample_name',
            'signature' => 'Signature',
            'date' => 'current_date',
            default => ''
        };
    }
}
