<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplateElementHolder;
use App\CertificateTemplateSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CertificateTemplateElementHolderController extends Controller
{
    /**
     * Store a newly created element holder.
     */
    public function store(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $request->validate([
            'holder_type' => 'required|in:field,text',
            'max_elements' => 'required|integer|min:1|max:50',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        // Get the highest sort order for this section
        $maxSortOrder = $section->elementHolders()->max('sort_order') ?? 0;

        $holder = CertificateTemplateElementHolder::create([
            'certificate_template_section_id' => $section->id,
            'holder_type' => $request->holder_type,
            'max_elements' => $request->max_elements,
            'sort_order' => $maxSortOrder + 1,
            'position_x' => $request->position_x ?? 50,
            'position_y' => $request->position_y ?? 50,
            'width' => $request->width ?? 300,
            'height' => $request->height ?? 200,
        ]);

        // Calculate percentages based on canvas size
        if ($request->has('canvas_width') && $request->has('canvas_height')) {
            $holder->update([
                'position_x_percent' => ($holder->position_x / $request->canvas_width) * 100,
                'position_y_percent' => ($holder->position_y / $request->canvas_height) * 100,
                'width_percent' => ($holder->width / $request->canvas_width) * 100,
                'height_percent' => ($holder->height / $request->canvas_height) * 100,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Element holder created successfully.',
            'holder' => $holder->load('elements')
        ]);
    }

    /**
     * Display the specified element holder.
     */
    public function show(CertificateTemplateElementHolder $holder): JsonResponse
    {
        return response()->json([
            'success' => true,
            'holder' => $holder->load(['elements', 'section'])
        ]);
    }

    /**
     * Update the specified element holder.
     */
    public function update(Request $request, CertificateTemplateElementHolder $holder): JsonResponse
    {
        $request->validate([
            'holder_type' => 'sometimes|in:field,text',
            'max_elements' => 'sometimes|integer|min:1|max:50',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'width' => 'nullable|numeric',
            'height' => 'nullable|numeric',
        ]);

        $holder->update($request->only([
            'holder_type',
            'max_elements',
            'position_x',
            'position_y',
            'width',
            'height',
            'position_x_percent',
            'position_y_percent',
            'width_percent',
            'height_percent',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Element holder updated successfully.',
            'holder' => $holder->fresh()->load('elements')
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
     * Clone an element holder with all its elements.
     */
    public function clone(CertificateTemplateElementHolder $holder): JsonResponse
    {
        DB::transaction(function () use ($holder, &$newHolder) {
            // Clone the holder
            $newHolder = $holder->replicate();
            $newHolder->sort_order = $holder->section->elementHolders()->max('sort_order') + 1;
            $newHolder->save();

            // Clone all elements
            foreach ($holder->elements as $element) {
                $newElement = $element->replicate();
                $newElement->certificate_template_element_holder_id = $newHolder->id;
                $newElement->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Element holder cloned successfully.',
            'holder' => $newHolder->load('elements')
        ]);
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
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Holder position updated successfully.',
            'holder' => $holder
        ]);
    }
}
