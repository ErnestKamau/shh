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

    public function index()
    {
        $templates = FormTemplate::latest()->paginate(10);
        return view('template-engine::index', compact('templates'));
    }

    public function create()
    {
        $processes = \App\AnalysisMethod::select('id', 'name')->get();
        return view('template-engine::create', compact('processes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'process_id' => 'nullable|integer',
        ]);

        if (!empty($data['process_id'])) {
            $data['process_type'] = \App\AnalysisMethod::class;
        }

        $template = $this->service->createTemplate($data, auth()->user());

        return redirect()->route('templates.builder', $template->id);
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
}
