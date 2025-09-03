<?php

namespace App\Http\Controllers;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplatePermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CertificateTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(CertificateTemplate::class, 'certificateTemplate');
    }
    /**
     * Display a listing of certificate templates
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = CertificateTemplate::with(['creator', 'sections'])
            ->withCount(['sections']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            } elseif ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by creator
        if ($request->filled('creator')) {
            $query->where('created_by', $request->get('creator'));
        }

        $templates = $query->orderBy('created_at', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        return view('certificate-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new certificate template
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('certificate-templates.create');
    }

    /**
     * Store a newly created certificate template
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:certificate_templates,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'page_settings' => ['nullable', 'array'],
            'header_settings' => ['nullable', 'array'],
            'footer_settings' => ['nullable', 'array'],
            'is_active' => ['boolean']
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_published'] = false; // New templates start as drafts
        $validated['version'] = '1.0';

        $template = CertificateTemplate::create($validated);

        return redirect()
            ->route('certificate-templates.show', $template)
            ->with('success', 'Certificate template created successfully. You can now add sections and elements.');
    }

    /**
     * Display the specified certificate template
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\View\View
     */
    public function show(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->load([
            'sections.elements',
            'sections.children.elements',
            'creator'
        ]);

        $statistics = $certificateTemplate->getStatistics();

        return view('certificate-templates.show', compact('certificateTemplate', 'statistics'));
    }

    /**
     * Show the form for editing the specified certificate template
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\View\View
     */
    public function edit(CertificateTemplate $certificateTemplate)
    {
        return view('certificate-templates.edit', compact('certificateTemplate'));
    }

    /**
     * Update the specified certificate template
     * 
     * @param Request $request
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, CertificateTemplate $certificateTemplate)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('certificate_templates', 'name')->ignore($certificateTemplate->id)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'page_settings' => ['nullable', 'array'],
            'header_settings' => ['nullable', 'array'],
            'footer_settings' => ['nullable', 'array'],
            'is_active' => ['boolean']
        ]);

        $certificateTemplate->update($validated);

        return redirect()
            ->route('certificate-templates.show', $certificateTemplate)
            ->with('success', 'Certificate template updated successfully.');
    }

    /**
     * Remove the specified certificate template
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(CertificateTemplate $certificateTemplate)
    {
        // Check if template has generated reports (when that functionality exists)
        // For now, we'll just check if it's published
        if ($certificateTemplate->is_published) {
            return redirect()
                ->route('certificate-templates.show', $certificateTemplate)
                ->with('error', 'Cannot delete published template. Please unpublish it first.');
        }

        $templateName = $certificateTemplate->name;
        $certificateTemplate->delete();

        return redirect()
            ->route('certificate-templates.index')
            ->with('success', "Certificate template '{$templateName}' deleted successfully.");
    }

    /**
     * Preview the template as it will appear in reports
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\View\View
     */
    public function preview(CertificateTemplate $certificateTemplate)
    {
        $certificateTemplate->load([
            'sections.elements' => function($query) {
                $query->orderBy('sort_order');
            },
            'sections.children.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Generate sample data for preview
        $sampleData = $this->generateSampleData();

        return view('certificate-templates.preview', compact('certificateTemplate', 'sampleData'));
    }

    /**
     * Toggle the published status of the template
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePublished(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('publish', $certificateTemplate);
        // Validate template has sections before publishing
        if (!$certificateTemplate->is_published && !$certificateTemplate->hasSections()) {
            return redirect()
                ->route('certificate-templates.show', $certificateTemplate)
                ->with('error', 'Cannot publish template without sections. Please add at least one section first.');
        }

        $certificateTemplate->update([
            'is_published' => !$certificateTemplate->is_published
        ]);

        $status = $certificateTemplate->is_published ? 'published' : 'unpublished';
        
        return redirect()
            ->route('certificate-templates.show', $certificateTemplate)
            ->with('success', "Template has been {$status} successfully.");
    }

    /**
     * Clone an existing template
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clone(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('clone', $certificateTemplate);
        $clonedTemplate = $certificateTemplate->duplicate();

        return redirect()
            ->route('certificate-templates.show', $clonedTemplate)
            ->with('success', 'Template cloned successfully. You can now modify the copy.');
    }

    /**
     * Export template structure as JSON
     */
    public function export(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('export', $certificateTemplate);
        $certificateTemplate->load([
            'sections.elements',
            'sections.children.elements'
        ]);

        $exportData = [
            'template' => $certificateTemplate->only([
                'name', 'description', 'version', 'page_settings', 
                'header_settings', 'footer_settings'
            ]),
            'sections' => $this->buildSectionHierarchy($certificateTemplate->rootSections)
        ];

        $filename = str_replace(' ', '_', strtolower($certificateTemplate->name)) . '_template_export.json';

        return response()->json($exportData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Build hierarchical section structure for export
     */
    private function buildSectionHierarchy($sections)
    {
        return $sections->map(function($section) {
            return [
                'title' => $section->title,
                'description' => $section->description,
                'sort_order' => $section->sort_order,
                'is_collapsible' => $section->is_collapsible,
                'styling_options' => $section->styling_options,
                'elements' => $section->elements->map(function($element) {
                    return $element->only([
                        'element_type', 'content', 'properties', 'styling',
                        'sort_order', 'is_conditional', 'conditional_logic'
                    ]);
                }),
                'children' => $this->buildSectionHierarchy($section->children)
            ];
        });
    }

    /**
     * Generate sample data for template preview
     */
    private function generateSampleData()
    {
        return [
            'client_name' => 'Sample Client Ltd.',
            'sample_id' => 'SMPL-2025-001',
            'date_received' => '2025-01-15',
            'date_analyzed' => '2025-01-16',
            'analyst_name' => 'Dr. Jane Smith',
            'test_parameter_1' => 'pH Level',
            'test_result_1' => '7.2',
            'test_unit_1' => 'pH units',
            'test_specification_1' => '6.5 - 8.5',
            'test_parameter_2' => 'Conductivity',
            'test_result_2' => '450',
            'test_unit_2' => 'μS/cm',
            'test_specification_2' => '< 500',
            'conclusion' => 'All parameters are within acceptable limits.',
            'remarks' => 'Sample tested according to standard procedures.'
        ];
    }

    /**
     * Manage template permissions
     * 
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\View\View
     */
    public function permissions(CertificateTemplate $certificateTemplate)
    {
        $this->authorize('managePermissions', $certificateTemplate);
        $certificateTemplate->load(['permissions.user']);
        
        // Get available users and roles for assignment
        $users = \App\User::orderBy('name')->get();
        $roles = \App\Role::orderBy('name')->get();

        return view('certificate-templates.permissions', compact('certificateTemplate', 'users', 'roles'));
    }

    /**
     * Update template permissions
     * 
     * @param Request $request
     * @param CertificateTemplate $certificateTemplate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePermissions(Request $request, CertificateTemplate $certificateTemplate)
    {
        $this->authorize('managePermissions', $certificateTemplate);
        
        $action = $request->input('action');
        
        if ($action === 'add') {
            $validated = $request->validate([
                'permission_type' => ['required', 'in:view,edit,publish,generate'],
                'assignment_type' => ['required', 'in:user,role'],
                'user_id' => ['required_if:assignment_type,user', 'exists:users,id'],
                'role_id' => ['required_if:assignment_type,role', 'exists:roles,id']
            ]);

            // Check if permission already exists
            $existingPermission = $certificateTemplate->permissions()
                ->where('user_id', $validated['assignment_type'] === 'user' ? $validated['user_id'] : null)
                ->where('role_id', $validated['assignment_type'] === 'role' ? $validated['role_id'] : null)
                ->first();

            if ($existingPermission) {
                // Update existing permission
                $existingPermission->update([
                    'permission_type' => $validated['permission_type']
                ]);
                $message = 'Permission updated successfully.';
            } else {
                // Create new permission
                $certificateTemplate->permissions()->create([
                    'user_id' => $validated['assignment_type'] === 'user' ? $validated['user_id'] : null,
                    'role_id' => $validated['assignment_type'] === 'role' ? $validated['role_id'] : null,
                    'permission_type' => $validated['permission_type']
                ]);
                $message = 'Permission added successfully.';
            }

            return redirect()
                ->route('certificate-templates.permissions', $certificateTemplate)
                ->with('success', $message);
        }
        
        if ($action === 'remove') {
            $validated = $request->validate([
                'permission_id' => ['required', 'exists:certificate_template_permissions,id']
            ]);

            $permission = $certificateTemplate->permissions()->find($validated['permission_id']);
            if ($permission) {
                $permission->delete();
                return redirect()
                    ->route('certificate-templates.permissions', $certificateTemplate)
                    ->with('success', 'Permission removed successfully.');
            }
        }

        return redirect()
            ->route('certificate-templates.permissions', $certificateTemplate)
            ->with('error', 'Invalid action or permission not found.');
    }
}