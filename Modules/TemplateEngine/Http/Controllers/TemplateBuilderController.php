<?php

namespace Modules\TemplateEngine\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\TemplateEngine\Services\TemplateCreationService;
use Modules\TemplateEngine\Services\DatabaseMetadataService;
use Modules\TemplateEngine\Models\FormTemplate;

class TemplateBuilderController extends Controller
{
    protected $service;
    protected $metadataService;

    public function __construct(TemplateCreationService $service, DatabaseMetadataService $metadataService)
    {
        $this->service = $service;
        $this->metadataService = $metadataService;
    }

    public function index(Request $request)
    {
        $query = FormTemplate::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // Get per page value (default to 25, validate against allowed values)
        $perPage = $request->get('per_page', 25);
        $allowedPerPage = [10, 25, 50, 100];
        if (!in_array((int)$perPage, $allowedPerPage)) {
            $perPage = 25;
        }
        
        $templates = $query->with('creator')->latest()->paginate((int)$perPage)->withQueryString();
        
        // Get unique categories for filter dropdown
        $categories = FormTemplate::distinct()->whereNotNull('category')->pluck('category')->sort()->values();
        
        return view('template-engine::index', compact('templates', 'categories'));
    }

    public function create()
    {
        return view('template-engine::create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:form,report',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'process_id' => 'nullable|string|in:' . implode(',', array_keys(getTemplateProcesses())),
        ]);

        // Store process identifier in process_type since process_id is for polymorphic relationships
        if (!empty($data['process_id'])) {
            $data['process_type'] = $data['process_id'];
            $data['process_id'] = null; // Set to null since we're using process_type for the identifier
        }

        $template = $this->service->createTemplate($data, auth()->user());

        return redirect()->route('templates.builder', $template->id);
    }

    public function edit($id)
    {
        $template = FormTemplate::findOrFail($id);
        return view('template-engine::edit', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = FormTemplate::findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:form,report',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'process_id' => 'nullable|string|in:' . implode(',', array_keys(getTemplateProcesses())),
        ]);

        // Store process identifier in process_type since process_id is for polymorphic relationships
        if (!empty($data['process_id'])) {
            $data['process_type'] = $data['process_id'];
            $data['process_id'] = null; // Set to null since we're using process_type for the identifier
        } else {
             $data['process_type'] = null;
             $data['process_id'] = null;
        }
        
        $template->update($data);

        return redirect()->route('templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function builder($id)
    {
        $template = FormTemplate::with(['sections.fields.options', 'sections.fields.datasetBinding'])->findOrFail($id);
        $tables = $this->metadataService->getTables();
        
        return view('template-engine::builder', compact('template', 'tables'));
    }

    public function preview($id)
    {
        $template = FormTemplate::with(['sections.fields.options', 'sections.fields.datasetBinding'])->findOrFail($id);
        return view('template-engine::preview', compact('template'));
    }

    public function destroy($id)
    {
        $template = FormTemplate::findOrFail($id);
        
        // Delete the template (soft delete if SoftDeletes is used, otherwise hard delete)
        $template->delete();
        
        return redirect()->route('templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function showSubmission($templateId, $submissionId)
    {
        $template = FormTemplate::with(['sections.fields.options'])->findOrFail($templateId);
        $submission = $template->submissions()->findOrFail($submissionId);
        
        return view('template-engine::submissions.show', compact('template', 'submission'));
    }
}
