<?php

namespace App\Http\Controllers;

use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\CertificateTemplateElement;
use App\CertificateTemplateReport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade as PDF;

class CertificateTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $submissionFormId = $request->get('submission_form_id');
        
        $query = CertificateTemplate::with(['creator', 'sections', 'submissionForm'])
            ->withCount(['sections', 'reports']);
            
        if ($submissionFormId) {
            $query->where('submission_form_id', $submissionFormId);
        }
        
        $templates = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get all submission forms for the filter dropdown
        $submissionForms = \App\Models\SubmissionForm::where('is_published', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('certificate-templates.index', compact('templates', 'submissionForms', 'submissionFormId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        // Get all published and active submission forms for the dropdown
        $submissionForms = \App\Models\SubmissionForm::where('is_published', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        // If a submission form ID is provided in the URL, pre-select it
        $selectedSubmissionFormId = $request->get('submission_form_id');
        
        return view('certificate-templates.create', compact('submissionForms', 'selectedSubmissionFormId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'submission_form_id' => 'required|exists:submission_forms,id',
            'page_settings' => 'nullable|array',
            'header_settings' => 'nullable|array',
            'footer_settings' => 'nullable|array'
        ]);

        $template = CertificateTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'submission_form_id' => $request->submission_form_id,
            'page_settings' => $request->page_settings ?? [],
            'header_settings' => $request->header_settings ?? [],
            'footer_settings' => $request->footer_settings ?? [],
            'created_by' => Auth::id()
        ]);

        return redirect()
            ->route('certificate-templates.show', $template)
            ->with('success', 'Certificate template created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CertificateTemplate $certificateTemplate): View
    {
        $template = $certificateTemplate->load([
            'creator',
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'reports' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }
        ]);

        return view('certificate-templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CertificateTemplate $certificateTemplate): View
    {
        $template = $certificateTemplate->load([
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('certificate-templates.edit', compact('template'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_published' => 'boolean',
            'is_active' => 'boolean',
            'page_settings' => 'nullable|array',
            'header_settings' => 'nullable|array',
            'footer_settings' => 'nullable|array'
        ]);

        $certificateTemplate->update($request->only([
            'name', 'description', 'is_published', 'is_active',
            'page_settings', 'header_settings', 'footer_settings'
        ]));

        return redirect()
            ->route('certificate-templates.show', $certificateTemplate)
            ->with('success', 'Certificate template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        $certificateTemplate->delete();

        return redirect()
            ->route('certificate-templates.index')
            ->with('success', 'Certificate template deleted successfully.');
    }

    /**
     * Toggle the published status of the template.
     */
    public function togglePublished(CertificateTemplate $certificateTemplate): JsonResponse
    {
        $certificateTemplate->update([
            'is_published' => !$certificateTemplate->is_published
        ]);

        return response()->json([
            'success' => true,
            'is_published' => $certificateTemplate->is_published,
            'message' => $certificateTemplate->is_published 
                ? 'Template published successfully.' 
                : 'Template unpublished successfully.'
        ]);
    }

    /**
     * Toggle the active status of the template.
     */
    public function toggleActive(CertificateTemplate $certificateTemplate): JsonResponse
    {
        $certificateTemplate->update([
            'is_active' => !$certificateTemplate->is_active
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $certificateTemplate->is_active,
            'message' => $certificateTemplate->is_active 
                ? 'Template activated successfully.' 
                : 'Template deactivated successfully.'
        ]);
    }

    /**
     * Duplicate a template.
     */
    public function duplicate(CertificateTemplate $certificateTemplate): RedirectResponse
    {
        DB::transaction(function () use ($certificateTemplate) {
            // Create the new template
            $newTemplate = $certificateTemplate->replicate();
            $newTemplate->name = $certificateTemplate->name . ' (Copy)';
            $newTemplate->is_published = false;
            $newTemplate->created_by = Auth::id();
            $newTemplate->save();

            // Duplicate sections and elements
            $this->duplicateSections($certificateTemplate, $newTemplate);
        });

        return redirect()
            ->route('certificate-templates.index')
            ->with('success', 'Template duplicated successfully.');
    }

    /**
     * Duplicate sections and their elements.
     */
    private function duplicateSections(CertificateTemplate $originalTemplate, CertificateTemplate $newTemplate): void
    {
        $sections = $originalTemplate->sections()->whereNull('parent_section_id')->get();
        
        foreach ($sections as $section) {
            $this->duplicateSection($section, $newTemplate);
        }
    }

    /**
     * Duplicate a single section and its children.
     */
    private function duplicateSection(CertificateTemplateSection $originalSection, CertificateTemplate $newTemplate, ?int $parentId = null): CertificateTemplateSection
    {
        $newSection = $originalSection->replicate();
        $newSection->certificate_template_id = $newTemplate->id;
        $newSection->parent_section_id = $parentId;
        $newSection->save();

        // Duplicate elements
        foreach ($originalSection->elements as $element) {
            $newElement = $element->replicate();
            $newElement->certificate_template_section_id = $newSection->id;
            $newElement->save();
        }

        // Duplicate child sections
        foreach ($originalSection->childSections as $childSection) {
            $this->duplicateSection($childSection, $newTemplate, $newSection->id);
        }

        return $newSection;
    }

    /**
     * Preview the template.
     */
    public function preview(CertificateTemplate $certificateTemplate): View
    {
        $template = $certificateTemplate->load([
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('certificate-templates.preview', compact('template'));
    }

    /**
     * Generate PDF preview.
     */
    public function generatePdfPreview(CertificateTemplate $certificateTemplate)
    {
        $template = $certificateTemplate->load([
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        $pdf = PDF::loadView('certificate-templates.pdf.preview', compact('template'));
        
        return $pdf->stream('template-preview-' . $certificateTemplate->id . '.pdf');
    }

    /**
     * Get template data for AJAX requests.
     */
    public function getTemplateData(CertificateTemplate $certificateTemplate): JsonResponse
    {
        $template = $certificateTemplate->load([
            'sections.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return response()->json([
            'success' => true,
            'template' => $template
        ]);
    }

    /**
     * Upload image for certificate templates.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,svg|max:5120', // 5MB max
        ]);

        try {
            $image = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            
            // Store in public/storage/certificate-images directory
            $path = $image->storeAs('certificate-images', $filename, 'public');
            
            // Generate full URL
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'url' => $url,
                'path' => $path,
                'filename' => $filename,
                'size' => $image->getSize(),
                'mime_type' => $image->getMimeType()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available submission form instances for report generation.
     */
    public function getSubmissionFormInstances(): JsonResponse
    {
        $instances = \App\SubmissionFormInstance::with(['submissionForm'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'instances' => $instances
        ]);
    }

    /**
     * Generate a report from a template.
     */
    public function generateReport(Request $request, CertificateTemplate $certificateTemplate): JsonResponse
    {
        $request->validate([
            'submission_form_instance_id' => 'required|exists:submission_form_instances,id'
        ]);

        $report = CertificateTemplateReport::create([
            'certificate_template_id' => $certificateTemplate->id,
            'submission_form_instance_id' => $request->submission_form_instance_id,
            'generated_by' => Auth::id(),
            'status' => CertificateTemplateReport::STATUS_PENDING
        ]);

        // Queue the report generation job
        // dispatch(new GenerateCertificateReportJob($report));

        return response()->json([
            'success' => true,
            'report_id' => $report->id,
            'message' => 'Report generation started. You will be notified when it\'s ready.'
        ]);
    }
}
