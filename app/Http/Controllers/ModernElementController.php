<?php

namespace App\Http\Controllers;

use App\CertificateTemplateElement;
use App\CertificateTemplateSection;
use App\Models\ModernCertificateTemplateElement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ModernElementController extends Controller
{
    /**
     * Show element.
     */
    public function show(CertificateTemplateElement $element): JsonResponse
    {
        return response()->json([
            'success' => true,
            'element' => $element->load('section')
        ]);
    }

    /**
     * Create element with CSS/data config.
     */
    public function store(Request $request, CertificateTemplateSection $section): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'element_type' => 'required|string',
            'content' => 'nullable|string',
            'parent_cell_id' => 'nullable|string',
            'css_config' => 'nullable|array',
            'data_config' => 'nullable|array',
            'properties' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0'
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
            'parent_cell_id' => $request->parent_cell_id,
            'css_config' => $request->css_config ?? [],
            'data_config' => $request->data_config ?? [],
            'properties' => $request->properties ?? [],
            'sort_order' => $request->sort_order ?? 0
        ]);

        return response()->json([
            'success' => true,
            'element' => $element->fresh(),
            'message' => 'Element created successfully.'
        ]);
    }

    /**
     * Update element properties.
     */
    public function update(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'nullable|string',
            'parent_cell_id' => 'nullable|string',
            'properties' => 'nullable|array',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $element->update($request->only([
            'content', 'parent_cell_id', 'properties', 'sort_order'
        ]));

        return response()->json([
            'success' => true,
            'element' => $element->fresh(),
            'message' => 'Element updated successfully.'
        ]);
    }

    /**
     * Delete element.
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
     * Update element position in cell.
     */
    public function updatePosition(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'parent_cell_id' => 'required|string',
            'sort_order' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $element->update([
            'parent_cell_id' => $request->parent_cell_id,
            'sort_order' => $request->sort_order
        ]);

        return response()->json([
            'success' => true,
            'element' => $element->fresh(),
            'message' => 'Element position updated successfully.'
        ]);
    }

    /**
     * Update CSS configuration.
     */
    public function updateCssConfig(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'css_config' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernElement = new ModernCertificateTemplateElement($element);
        $modernElement->setCssConfig($request->css_config);

        return response()->json([
            'success' => true,
            'css_config' => $modernElement->getCssConfig(),
            'inline_css' => $modernElement->generateInlineCss(),
            'message' => 'CSS configuration updated successfully.'
        ]);
    }

    /**
     * Update data configuration.
     */
    public function updateDataConfig(Request $request, CertificateTemplateElement $element): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data_config' => 'required|array',
            'data_config.type' => 'required|in:static,dynamic_model,dynamic_derived'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $modernElement = new ModernCertificateTemplateElement($element);
        $modernElement->setDataConfig($request->data_config);

        return response()->json([
            'success' => true,
            'data_config' => $modernElement->getDataConfig(),
            'message' => 'Data configuration updated successfully.'
        ]);
    }
}
