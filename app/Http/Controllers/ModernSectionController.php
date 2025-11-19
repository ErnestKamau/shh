<?php

namespace App\Http\Controllers;

use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\Models\ModernCertificateTemplateSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ModernSectionController extends Controller
{
    /**
     * Show section.
     */
    public function show(CertificateTemplateSection $section): JsonResponse
    {
        return response()->json([
            'success' => true,
            'section' => $section->load(['template', 'elements'])
        ]);
    }

    /**
     * Create section with layout structure.
     */
    public function store(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_section_id' => 'nullable|exists:certificate_template_sections,id',
            'sort_order' => 'nullable|integer|min:0',
            'layout_structure' => 'nullable|array',
            'css_config' => 'nullable|array',
            'data_config' => 'nullable|array'
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
            'styling_options' => $request->styling_options ?? [],
            'layout_structure' => $request->layout_structure ?? ['rows' => []],
            'css_config' => $request->css_config ?? [],
            'data_config' => $request->data_config ?? []
        ]);

        return response()->json([
            'success' => true,
            'section' => $section->fresh(),
            'message' => 'Section created successfully.'
        ]);
    }

    /**
     * Update section and layout.
     */
    public function update(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'layout_structure' => 'nullable|array',
            'css_config' => 'nullable|array',
            'data_config' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $section->update($request->only([
            'title', 'description', 'sort_order', 'is_collapsible',
            'styling_options', 'layout_structure', 'css_config', 'data_config'
        ]));

        return response()->json([
            'success' => true,
            'section' => $section->fresh(),
            'message' => 'Section updated successfully.'
        ]);
    }

    /**
     * Delete section.
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
     * Reorder sections.
     */
    public function reorder(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:certificate_template_sections,id',
            'sections.*.sort_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->sections as $sectionData) {
            CertificateTemplateSection::where('id', $sectionData['id'])
                ->update(['sort_order' => $sectionData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully.'
        ]);
    }

    /**
     * Add row to section.
     */
    public function addRow(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'row_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernSection = new ModernCertificateTemplateSection($section);
        $rowId = $modernSection->addRow($request->input('row_data', []));

        return response()->json([
            'success' => true,
            'row_id' => $rowId,
            'layout_structure' => $modernSection->getLayoutStructure(),
            'message' => 'Row added successfully.'
        ]);
    }

    /**
     * Add column to row.
     */
    public function addColumn(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'row_id' => 'required|string',
            'column_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernSection = new ModernCertificateTemplateSection($section);
        $columnId = $modernSection->addColumn($request->input('row_id'), $request->input('column_data', []));

        return response()->json([
            'success' => true,
            'column_id' => $columnId,
            'layout_structure' => $modernSection->getLayoutStructure(),
            'message' => 'Column added successfully.'
        ]);
    }

    /**
     * Add cell to column.
     */
    public function addCell(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'column_id' => 'required|string',
            'cell_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernSection = new ModernCertificateTemplateSection($section);
        $cellId = $modernSection->addCell($request->input('column_id'), $request->input('cell_data', []));

        return response()->json([
            'success' => true,
            'cell_id' => $cellId,
            'layout_structure' => $modernSection->getLayoutStructure(),
            'message' => 'Cell added successfully.'
        ]);
    }

    /**
     * Add nested section to cell.
     */
    public function addSubSection(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cell_id' => 'required|string',
            'sub_section_id' => 'required|exists:certificate_template_sections,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernSection = new ModernCertificateTemplateSection($section);
        $success = $modernSection->addSubSectionToCell(
            $request->input('cell_id'),
            $request->input('sub_section_id')
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'layout_structure' => $modernSection->getLayoutStructure(),
                'message' => 'Sub-section added to cell successfully.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to add sub-section to cell.'
        ], 400);
    }
}
