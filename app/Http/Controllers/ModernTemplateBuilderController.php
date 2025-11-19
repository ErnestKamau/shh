<?php

namespace App\Http\Controllers;

use App\CertificateTemplate;
use App\Models\ModernCertificateTemplateSection;
use App\Services\LayoutRendererService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ModernTemplateBuilderController extends Controller
{
    public function __construct()
    {
        // Services will be resolved via service container
    }

    /**
     * Show the modern template builder interface.
     */
    public function builder(CertificateTemplate $certificateTemplate): View
    {
        // Eager load sections with layout structure and elements
        $template = $certificateTemplate->load([
            'sections' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sections.childSections' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('certificate-templates.modern-builder', compact('template'));
    }

    /**
     * Save complete layout structure.
     */
    public function saveLayout(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'layout' => 'required|array',
            'layout.sections' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $layout = $request->input('layout');
            
            // Update each section's layout structure
            foreach ($layout['sections'] as $sectionData) {
                $section = $certificateTemplate->sections()->find($sectionData['id']);
                if ($section) {
                    $modernSection = new ModernCertificateTemplateSection($section);
                    $modernSection->setLayoutStructure($sectionData['layout_structure'] ?? []);
                    
                    if (isset($sectionData['css_config'])) {
                        $modernSection->setCssConfig($sectionData['css_config']);
                    }
                    
                    if (isset($sectionData['data_config'])) {
                        $modernSection->setDataConfig($sectionData['data_config']);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Layout saved successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load layout structure.
     */
    public function loadLayout(CertificateTemplate $certificateTemplate): JsonResponse
    {
        try {
            $template = $certificateTemplate->load([
                'sections' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'sections.elements' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'sections.childSections' => function ($query) {
                    $query->orderBy('sort_order');
                }
            ]);

            $sections = [];
            foreach ($template->sections as $section) {
                $modernSection = new ModernCertificateTemplateSection($section);
                $sections[] = [
                    'id' => $section->id,
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => $section->sort_order,
                    'parent_section_id' => $section->parent_section_id,
                    'layout_structure' => $modernSection->getLayoutStructure(),
                    'css_config' => $modernSection->getCssConfig(),
                    'data_config' => $modernSection->getDataConfig(),
                    'elements' => $section->elements->map(function ($element) {
                        return [
                            'id' => $element->id,
                            'element_type' => $element->element_type,
                            'content' => $element->content,
                            'parent_cell_id' => $element->parent_cell_id,
                            'css_config' => $element->css_config ?? [],
                            'data_config' => $element->data_config ?? []
                        ];
                    })
                ];
            }

            return response()->json([
                'success' => true,
                'layout' => [
                    'template_id' => $template->id,
                    'sections' => $sections
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load layout: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate preview HTML.
     */
    public function preview(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'layout' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $renderer = app(LayoutRendererService::class);
            $html = $renderer->generateHtml($certificateTemplate, $request->input('layout'));

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export to PDF/HTML.
     */
    public function export(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:pdf,html',
            'layout' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $renderer = app(LayoutRendererService::class);
            $format = $request->input('format');
            $html = $renderer->generateHtml($certificateTemplate, $request->input('layout'));

            if ($format === 'pdf') {
                $pdf = $renderer->generatePdf($html);
                return response()->json([
                    'success' => true,
                    'pdf_base64' => base64_encode($pdf),
                    'message' => 'PDF generated successfully.'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'message' => 'HTML generated successfully.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export: ' . $e->getMessage()
            ], 500);
        }
    }
}
