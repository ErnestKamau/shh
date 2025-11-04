<?php

namespace App\Http\Controllers;

use App\CertificateTemplateSection;
use App\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class CertificateTemplateSectionController extends Controller
{
    /**
     * Store a newly created section.
     */
    public function store(Request $request, CertificateTemplate $template): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_section_id' => 'nullable|exists:certificate_template_sections,id',
            'is_collapsible' => 'boolean',
        ]);

        // Get the highest sort order
        $maxSortOrder = $template->sections()->max('sort_order') ?? 0;

        $section = CertificateTemplateSection::create([
            'certificate_template_id' => $template->id,
            'parent_section_id' => $request->parent_section_id,
            'title' => $request->title,
            'description' => $request->description,
            'sort_order' => $maxSortOrder + 1,
            'is_collapsible' => $request->is_collapsible ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully.',
            'section' => $section->load('elementHolders')
        ]);
    }

    /**
     * Display the specified section.
     */
    public function show(CertificateTemplateSection $section): JsonResponse
    {
        return response()->json([
            'success' => true,
            'section' => $section->load(['elementHolders.elements', 'childSections'])
        ]);
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_collapsible' => 'boolean',
        ]);

        $section->update($request->only([
            'title',
            'description',
            'is_collapsible',
            'styling_options'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully.',
            'section' => $section->fresh()
        ]);
    }

    /**
     * Remove the specified section.
     */
    public function destroy(CertificateTemplateSection $section): JsonResponse
    {
        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully.'
        ]);
    }

    /**
     * Reorder sections within a template.
     */
    public function reorder(Request $request, CertificateTemplate $template): JsonResponse
    {
        $request->validate([
            'section_ids' => 'required|array',
            'section_ids.*' => 'required|integer|exists:certificate_template_sections,id'
        ]);

        DB::transaction(function () use ($request, $template) {
            foreach ($request->section_ids as $index => $sectionId) {
                CertificateTemplateSection::where('id', $sectionId)
                    ->where('certificate_template_id', $template->id)
                    ->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully.'
        ]);
    }

    /**
     * Resize a section (update height).
     */
    public function resize(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $request->validate([
            'height' => 'required|numeric|min:100|max:5000'
        ]);
        
        $section->update(['height' => $request->height]);
        
        return response()->json([
            'success' => true,
            'message' => 'Section resized successfully',
            'section' => $section
        ]);
    }
}
