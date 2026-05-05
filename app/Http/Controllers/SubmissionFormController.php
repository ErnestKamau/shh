<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormPermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Services\PageLayoutRegistry;

class SubmissionFormController extends Controller
{
    /**
     * Display a listing of submission forms
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = SubmissionForm::with(['creator', 'sections', 'sampleAnalysisStages'])
            ->withCount(['sections', 'instances']);

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

        $forms = $query->orderBy('created_at', 'desc')
                      ->paginate(15)
                      ->withQueryString();

        return view('submission-forms.index', compact('forms'));
    }

    /**
     * Show the form for creating a new submission form
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $labSections = \App\SampleAnalysisStage::where('active', 1)
            ->where('is_sample_stage', 0)
            ->orderBy('name')
            ->get();
        $availablePages = $this->getAvailablePlacementPages();

        return view('submission-forms.create', compact('labSections', 'availablePages'));
    }

    /**
     * Store a newly created submission form
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $availablePageNames = $this->getAvailablePlacementPageNames();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:submission_forms,name'],
            'document_code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'naming_convention_prefix' => ['required', 'string', 'max:50'],
            'naming_convention_format' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'is_customer_portal_form' => ['boolean'],
            'lims_destination_pages' => ['nullable', 'array'],
            'lims_destination_pages.*' => ['string', Rule::in($availablePageNames)],
            'start_submission_number' => ['nullable', 'integer', 'min:1'],
            'version' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'print_template_name' => ['nullable', 'string', 'max:255'],
            'target_pages' => ['nullable', 'array'],
            'target_pages.*' => ['string', Rule::in($availablePageNames)],
            'placement_mode' => ['required', Rule::in(['button_trigger', 'page_section'])],
            'display_mode' => ['nullable', Rule::in(['expanded', 'collapsible'])],
            'placement_slot' => ['nullable', 'array'],
            'trigger_button_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids.*' => ['exists:sample_analysis_stages,id']
        ]);

        $validated['target_pages'] = array_values($validated['target_pages'] ?? []);
        $validated['display_mode'] = $validated['display_mode'] ?? 'expanded';
        $validated['is_customer_portal_form'] = $request->boolean('is_customer_portal_form');
        $validated['lims_destination_pages'] = $validated['is_customer_portal_form']
            ? array_values($validated['lims_destination_pages'] ?? ['sample-workflow'])
            : [];

        $validated['created_by'] = Auth::id();
        $validated['is_published'] = false; // New forms start as drafts
        $validated['version'] = '1.0';

        $form = SubmissionForm::create($validated);

        if (isset($validated['sample_analysis_stage_ids'])) {
            $form->sampleAnalysisStages()->sync($validated['sample_analysis_stage_ids']);
        }

        $this->bustSubmissionFormPageCache($validated['target_pages'] ?? []);

        return redirect()
            ->route('submission-forms.show', $form)
            ->with('success', 'Submission form created successfully. You can now add sections and elements.');
    }

    /**
     * Display the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function show(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements',
            'creator'
        ]);

        $statistics = $submissionForm->getStatistics();

        return view('submission-forms.show', compact('submissionForm', 'statistics'));
    }

    /**
     * Show the form for editing the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function edit(SubmissionForm $submissionForm)
    {
        $labSections = \App\SampleAnalysisStage::where('active', 1)
            ->where('is_sample_stage', 0)
            ->orderBy('name')
            ->get();

        $availablePages = $this->getAvailablePlacementPages();
        $submissionForm->load('sampleAnalysisStages');

        return view('submission-forms.edit', compact('submissionForm', 'labSections', 'availablePages'));
    }

    /**
     * Update the specified submission form
     * 
     * @param Request $request
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, SubmissionForm $submissionForm)
    {
        $availablePageNames = $this->getAvailablePlacementPageNames();

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('submission_forms', 'name')->ignore($submissionForm->id)
            ],
            'document_code' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'naming_convention_prefix' => ['required', 'string', 'max:50'],
            'naming_convention_format' => ['required', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'is_customer_portal_form' => ['boolean'],
            'lims_destination_pages' => ['nullable', 'array'],
            'lims_destination_pages.*' => ['string', Rule::in($availablePageNames)],
            'start_submission_number' => ['nullable', 'integer', 'min:1'],
            'version' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'print_template_name' => ['nullable', 'string', 'max:255'],
            'target_pages' => ['nullable', 'array'],
            'target_pages.*' => ['string', Rule::in($availablePageNames)],
            'placement_mode' => ['required', Rule::in(['button_trigger', 'page_section'])],
            'display_mode' => ['nullable', Rule::in(['expanded', 'collapsible'])],
            'placement_slot' => ['nullable', 'array'],
            'trigger_button_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids.*' => ['exists:sample_analysis_stages,id']
        ]);

        $validated['target_pages'] = array_values($validated['target_pages'] ?? []);
        $validated['display_mode'] = $validated['display_mode'] ?? 'expanded';
        $validated['is_customer_portal_form'] = $request->boolean('is_customer_portal_form');
        $validated['lims_destination_pages'] = $validated['is_customer_portal_form']
            ? array_values($validated['lims_destination_pages'] ?? ['sample-workflow'])
            : [];

        $submissionForm->update($validated);

        $this->bustSubmissionFormPageCache($validated['target_pages'] ?? []);

        if (isset($validated['sample_analysis_stage_ids'])) {
            $submissionForm->sampleAnalysisStages()->sync($validated['sample_analysis_stage_ids']);
        } else {
            // If the field is present in request but empty (unselected all), sync empty array
            // If it's not present (e.g. API call that didn't include it), we might want to check for presence
            if ($request->has('sample_analysis_stage_ids')) {
                $submissionForm->sampleAnalysisStages()->sync([]);
            }
        }

        return redirect()
            ->route('submission-forms.show', $submissionForm)
            ->with('success', 'Submission form updated successfully.');
    }

    /**
     * Build a list of routable pages where a submission form can be embedded.
     *
     * @return array<int, array<string, string>>
     */
    private function getAvailablePlacementPages(): array
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) {
                if (!in_array('GET', $route->methods(), true)) {
                    return false;
                }

                $name = $route->getName();
                if (empty($name)) {
                    return false;
                }

                $uri = (string) $route->uri();
                if (str_starts_with($uri, '_ignition') || str_starts_with($uri, 'telescope')) {
                    return false;
                }

                return true;
            })
            ->map(function ($route) {
                $name = (string) $route->getName();
                $uri = '/' . ltrim((string) $route->uri(), '/');

                return [
                    'value' => $name,
                    'label' => $name . ' (' . $uri . ')',
                    'name' => $name,
                    'uri' => $uri,
                ];
            })
            ->unique('value')
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Get valid route-name values for placement validation.
     *
     * @return array<int, string>
     */
    private function getAvailablePlacementPageNames(): array
    {
        return collect($this->getAvailablePlacementPages())
            ->pluck('value')
            ->values()
            ->all();
    }

    /**
     * Bust the per-route submission form page cache.
     * Called after any change that affects which forms appear on a page.
     *
     * @param array $targetPages  Route names this form targets; empty means all routes.
     */
    private function bustSubmissionFormPageCache(array $targetPages): void
    {
        $routesToClear = empty($targetPages)
            ? array_keys(PageLayoutRegistry::getManifest())
            : $targetPages;

        foreach ($routesToClear as $routeName) {
            \Illuminate\Support\Facades\Cache::forget('sf_page_forms_' . $routeName);
        }
    }

    /**
     * Return slots and trigger-buttons for the given route names.
     * Called via AJAX from the create/edit form when the admin selects target pages.
     *
     * GET /submission-forms/page-layout?routes[]=dashboard-lab&routes[]=sample-workflow
     */
    public function getPageLayout(Request $request)
    {
        $routes = array_filter((array) $request->get('routes', []), fn($r) => is_string($r) && strlen($r) <= 255);
        $layout = PageLayoutRegistry::getLayoutForRoutes(array_values($routes));
        return response()->json(['success' => true, 'layout' => $layout]);
    }

    /**
     * Remove the specified submission form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(SubmissionForm $submissionForm)
    {
        // Check if form has instances
        if ($submissionForm->instances()->exists()) {
            return redirect()
                ->route('submission-forms.show', $submissionForm)
                ->with('error', 'Cannot delete form that has submission instances. Please archive it instead.');
        }

        $formName = $submissionForm->name;
        $submissionForm->delete();

        return redirect()
            ->route('submission-forms.index')
            ->with('success', "Submission form '{$formName}' deleted successfully.");
    }

    /**
     * Preview the form as users will see it
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\View\View
     */
    public function preview(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('submission-forms.preview', compact('submissionForm'));
    }

    /**
     * Toggle the published status of the form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function togglePublished(SubmissionForm $submissionForm)
    {
        // Validate form has sections before publishing
        if (!$submissionForm->is_published && !$submissionForm->hasSections()) {
            return redirect()
                ->route('submission-forms.show', $submissionForm)
                ->with('error', 'Cannot publish form without sections. Please add at least one section first.');
        }

        $submissionForm->update([
            'is_published' => !$submissionForm->is_published
        ]);

        $this->bustSubmissionFormPageCache($submissionForm->target_pages ?? []);

        $status = $submissionForm->is_published ? 'published' : 'unpublished';
        
        return redirect()
            ->route('submission-forms.show', $submissionForm)
            ->with('success', "Form has been {$status} successfully.");
    }

    /**
     * Clone an existing form
     * 
     * @param SubmissionForm $submissionForm
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clone(SubmissionForm $submissionForm)
    {
        $clonedForm = $submissionForm->replicate([
            'created_by',
            'is_published',
            'created_at',
            'updated_at'
        ]);

        $clonedForm->name = $submissionForm->name . ' (Copy)';
        $clonedForm->created_by = Auth::id();
        $clonedForm->is_published = false;
        $clonedForm->save();

        // Clone sections, holders, and elements
        foreach ($submissionForm->sections as $section) {
            $clonedSection = $section->replicate(['submission_form_id']);
            $clonedSection->submission_form_id = $clonedForm->id;
            $clonedSection->save();

            foreach ($section->elementHolders as $holder) {
                $clonedHolder = $holder->replicate(['submission_form_section_id']);
                $clonedHolder->submission_form_section_id = $clonedSection->id;
                $clonedHolder->save();

                foreach ($holder->elements as $element) {
                    $clonedElement = $element->replicate(['submission_form_element_holder_id']);
                    $clonedElement->submission_form_element_holder_id = $clonedHolder->id;
                    $clonedElement->save();
                }
            }
        }

        return redirect()
            ->route('submission-forms.show', $clonedForm)
            ->with('success', 'Form cloned successfully. You can now modify the copy.');
    }

    /**
     * Export form structure as JSON
     */
    public function export(SubmissionForm $submissionForm)
    {
        $submissionForm->load([
            'sections.elementHolders.elements'
        ]);

        $exportData = [
            'form' => $submissionForm->only([
                'name', 'description', 'naming_convention_prefix', 
                'naming_convention_format', 'version'
            ]),
            'sections' => $submissionForm->sections->map(function($section) {
                return [
                    'title' => $section->title,
                    'description' => $section->description,
                    'sort_order' => $section->sort_order,
                    'element_holders' => $section->elementHolders->map(function($holder) {
                        return [
                            'holder_type' => $holder->holder_type,
                            'max_elements' => $holder->max_elements,
                            'sort_order' => $holder->sort_order,
                            'elements' => $holder->elements->map(function($element) {
                                return $element->only([
                                    'element_type', 'label', 'name', 'placeholder',
                                    'help_text', 'is_required', 'is_readonly',
                                    'default_value', 'validation_rules', 'options',
                                    'calculation_formula', 'conditional_logic', 'sort_order'
                                ]);
                            })
                        ];
                    })
                ];
            })
        ];

        $filename = str_replace(' ', '_', strtolower($submissionForm->name)) . '_export.json';

        return response()->json($exportData)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get dynamic options for custom elements
     */
    public function getDynamicOptions(Request $request)
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            Log::warning('Unauthenticated request to dynamic options');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $elementType = $request->get('element_type');
        $clientId = $request->get('client_id');
        $sampleTypeId = $request->get('sample_type_id');
        $storeId = $request->get('store_id');
        $clientUnitId = $request->get('client_unit_id');
        
        // Pagination and search parameters for client_select
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 100);
        $search = $request->get('search', '');

        Log::info('Dynamic options request', [
            'element_type' => $elementType,
            'client_id' => $clientId,
            'client_unit_id' => $clientUnitId,
            'user_id' => auth()->id()
        ]);

        // Validate element type
        $validTypes = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'client_submission_officers_select', 'analysis_type_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'company_sub_unit_select', 'user_select'];
        if (!in_array($elementType, $validTypes)) {
            Log::warning('Invalid element type requested', ['element_type' => $elementType]);
            return response()->json(['error' => 'Invalid element type'], 400);
        }

        $options = [];

        try {

        switch ($elementType) {
            case 'client_select':
                // Build query with search filter
                $query = \App\Models\CRM\CRMCustomer::where('active', 1);
                
                if (!empty($search)) {
                    $query->where('name', 'LIKE', "%{$search}%");
                }
                
                // Paginate results
                $paginator = $query->orderBy('name')
                    ->paginate($perPage, ['id', 'name'], 'page', $page);
                
                // Build options array
                foreach ($paginator->items() as $client) {
                    $options[] = [
                        'value' => $client->id,
                        'label' => $client->name
                    ];
                }
                
                // Add fallback if no clients found
                if (empty($options)) {
                    $options[] = [
                        'value' => '',
                        'label' => 'No clients available'
                    ];
                }
                
                // Return with pagination metadata
                return response()->json([
                    'options' => $options,
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'has_more' => $paginator->hasMorePages()
                    ]
                ]);
                break;

            case 'sample_type_select':
                $query = \App\SampleType::orderBy('name');
                
                // If submission form ID is present, filter by associated lab sections
                if ($request->has('submission_form_id')) {
                    $formId = $request->get('submission_form_id');
                    $form = \App\Models\SubmissionForm::with('sampleAnalysisStages')->find($formId);
                    
                    if ($form && $form->sampleAnalysisStages->isNotEmpty()) {
                        $stageIds = $form->sampleAnalysisStages->pluck('id')->toArray();
                        
                        $query->whereHas('sampleAnalysisStages', function($q) use ($stageIds) {
                            $q->whereIn('sample_analysis_stages.id', $stageIds);
                        });
                    }
                }
                
                $sampleTypes = $query->get();

                foreach ($sampleTypes as $sampleType) {
                    $options[] = [
                        'value' => $sampleType->id,
                        'label' => $sampleType->name
                    ];
                }
                
                // Add fallback if no sample types found
                if (empty($options)) {
                    $options[] = [
                        'value' => '',
                        'label' => 'No sample types available'
                    ];
                }
                break;

            case 'client_unit_select':
                if ($clientId) {
                    $units = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $clientId)
                        ->where('active', 1)
                        ->orderBy('name')
                        ->get();

                    foreach ($units as $unit) {
                        $options[] = [
                            'value' => $unit->id,
                            'label' => $unit->name
                        ];
                    }
                }
                break;

            case 'client_contact_select':
                if ($clientId) {
                    $contacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $clientId)
                        ->where('active', 1)
                        ->orderBy('first_name')
                        ->get();

                    foreach ($contacts as $contact) {
                        $fullName = trim($contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name);
                        $options[] = [
                            'value' => $contact->id,
                            'label' => $fullName . ' (' . $contact->email . ')'
                        ];
                    }
                }
                break;

            case 'client_submission_officers_select':
                if ($clientId) {
                    $officers = \App\Models\CRM\CustomerContact::where('crm_customer_id', $clientId)
                        ->where('active', 1)
                        ->where('can_submit_sample', 1)
                        ->orderBy('first_name')
                        ->get();

                    foreach ($officers as $officer) {
                        $fullName = trim($officer->first_name . ' ' . $officer->middle_name . ' ' . $officer->last_name);
                        $options[] = [
                            'value' => $officer->id,
                            'label' => $fullName . ' (' . $officer->email . ')'
                        ];
                    }
                }
                break;

            case 'analysis_type_select':
                if ($sampleTypeId) {
                    $analysisTypes = \App\AnalysisType::where('sample_type_id', $sampleTypeId)
                        ->where('active', 1)
                        ->where('company_id', getUserCompany())
                        ->orderBy('name')
                        ->get();

                    foreach ($analysisTypes as $analysisType) {
                        $options[] = [
                            'value' => $analysisType->id,
                            'label' => $analysisType->name . ' (' . $analysisType->code . ')'
                        ];
                    }
                }
                break;

            case 'store_select':
                $stores = \App\InventoryStore::where('company_id', getUserCompany())
                    ->orderBy('name')
                    ->get();

                foreach ($stores as $store) {
                    $options[] = [
                        'value' => $store->id,
                        'label' => $store->name
                    ];
                }
                break;

            case 'store_slot_select':
                if ($storeId) {
                    $slots = \App\InventoryStoreSlot::where('inventory_store_id', $storeId)
                        ->orderBy('name')
                        ->get();

                    foreach ($slots as $slot) {
                        $options[] = [
                            'value' => $slot->id,
                            'label' => $slot->name
                        ];
                    }
                }
                break;

            case 'sample_condition_select':
                $sampleConditions = \App\SampleCondition::where('active', 1)
                    ->orderBy('name')
                    ->get();

                foreach ($sampleConditions as $condition) {
                    $options[] = [
                        'value' => $condition->id,
                        'label' => $condition->name . ($condition->short_name ? ' (' . $condition->short_name . ')' : '')
                    ];
                }
                break;

            case 'standard_select':
                $standards = \App\Standards::where('status', 1)
                    ->orderBy('name')
                    ->get();

                foreach ($standards as $standard) {
                    $options[] = [
                        'value' => $standard->id,
                        'label' => $standard->name . ' (' . $standard->code . ')'
                    ];
                }
                break;

            case 'sample_point_select':
                if ($clientUnitId) {
                    $samplePoints = \App\Models\CRM\SamplePoint::where('active', 1)
                        ->where('crm_company_unit_id', $clientUnitId)
                        ->with('area')
                        ->orderBy('name')
                        ->get();

                    foreach ($samplePoints as $samplePoint) {
                        // Format: "area - sample point" if area exists, otherwise just "sample point"
                        $label = $samplePoint->area && $samplePoint->area->name 
                            ? $samplePoint->area->name . ' - ' . $samplePoint->name 
                            : $samplePoint->name;
                            
                        $options[] = [
                            'value' => $samplePoint->id,
                            'label' => $label
                        ];
                    }
                }
                break;

            case 'company_sub_unit_select':
                $subUnitQuery = \App\Models\CRM\CRMCompanySubUnit::where('active', 1);

                if ($clientUnitId) {
                    $subUnitQuery->where('crm_company_unit_id', $clientUnitId);
                }
                // Not yet implemented companies
                // if (function_exists('getUserCompany')) {
                //     $subUnitQuery->whereHas('companyUnit', function ($q) {
                //         $q->where('company_id', getUserCompany());
                //     });
                // }

                $subUnits = $subUnitQuery->orderBy('name')->get();

                foreach ($subUnits as $subUnit) {
                    $options[] = [
                        'value' => $subUnit->id,
                        'label' => $subUnit->name . ($subUnit->code ? ' (' . $subUnit->code . ')' : '')
                    ];
                }

                if (empty($options)) {
                    $options[] = [
                        'value' => '',
                        'label' => 'No company units available'
                    ];
                }
                break;

            case 'user_select':
                $query = \App\User::query()
                    ->where('active', 1)
                    ->where('is_client', 0)
                    ->whereNull('supplier_id');

                // if (auth()->check() && function_exists('getUserCompany')) {
                //     $query->where('company_id', getUserCompany());
                // }

                if (!empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%");
                    });
                }

                $paginator = $query->orderBy('name')
                    ->paginate($perPage, ['id', 'name', 'email'], 'page', $page);

                foreach ($paginator->items() as $user) {
                    $label = $user->name;
                    if (!empty($user->email)) {
                        $label .= ' (' . $user->email . ')';
                    }

                    $options[] = [
                        'value' => $user->id,
                        'label' => $label
                    ];
                }

                if (empty($options)) {
                    $options[] = [
                        'value' => '',
                        'label' => 'No users available'
                    ];
                }

                return response()->json([
                    'options' => $options,
                    'pagination' => [
                        'current_page' => $paginator->currentPage(),
                        'per_page' => $paginator->perPage(),
                        'total' => $paginator->total(),
                        'has_more' => $paginator->hasMorePages()
                    ]
                ]);
        }

        return response()->json(['options' => $options]);
        
        } catch (\Exception $e) {
            Log::error('Error loading dynamic options: ' . $e->getMessage(), [
                'element_type' => $elementType,
                'client_id' => $clientId,
                'user_id' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Failed to load options'], 500);
        }
    }

    /**
     * Quick store for creating client from modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStoreClient(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
            ]);

            $client = \App\Models\CRM\CRMCustomer::create([
                'name' => $validated['name'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'company_id' => getUserCompany(),
                'active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating client: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick store for creating client unit from modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStoreClientUnit(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:crm_customers,id',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
            ]);

            $unit = \App\Models\CRM\CRMCompanyUnit::create([
                'crm_customer_id' => $validated['client_id'],
                'name' => $validated['name'],
                'address' => $validated['address'] ?? null,
                'active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'unit' => [
                    'id' => $unit->id,
                    'name' => $unit->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating client unit: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client unit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick store for creating client contact from modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStoreClientContact(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_id' => 'required|exists:crm_customers,id',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'position' => 'nullable|string|max:255',
            ]);

            // Split name into first, middle, last
            $nameParts = explode(' ', $validated['name']);
            $firstName = $nameParts[0] ?? '';
            $lastName = count($nameParts) > 2 ? array_pop($nameParts) : (count($nameParts) > 1 ? $nameParts[1] : '');
            $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';

            $contact = \App\Models\CRM\CustomerContact::create([
                'crm_customer_id' => $validated['client_id'],
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'position' => $validated['position'] ?? null,
                'active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'contact' => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating client contact: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create client contact: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick store for creating sample condition from modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStoreSampleCondition(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $condition = \App\SampleCondition::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'condition' => [
                    'id' => $condition->id,
                    'name' => $condition->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating sample condition: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample condition: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick store for creating sample point from modal
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStoreSamplePoint(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'client_unit_id' => 'required|exists:crm_company_units,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            $point = \App\Models\CRM\SamplePoint::create([
                'crm_company_unit_id' => $validated['client_unit_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'active' => 1,
            ]);

            return response()->json([
                'success' => true,
                'point' => [
                    'id' => $point->id,
                    'name' => $point->name,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating sample point: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample point: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user signature image
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSignature(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Ensure user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $userId = $request->get('user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            $user = \App\User::find($userId);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $signaturePath = $user->electronic_sig;

            if (!$signaturePath || trim($signaturePath) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No signature available for this user'
                ], 404);
            }

            // Build full URL if path is relative
            $signatureUrl = $signaturePath;
            if (!filter_var($signaturePath, FILTER_VALIDATE_URL)) {
                $signatureUrl = asset($signaturePath);
            }

            return response()->json([
                'success' => true,
                'signature_path' => $signaturePath,
                'signature_url' => $signatureUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching user signature: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user signature: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getContactSignature(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Ensure user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $contactId = $request->get('contact_id');

            if (!$contactId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact ID is required'
                ], 400);
            }

            $contact = \App\Models\CRM\CustomerContact::find($contactId);

            if (!$contact) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found'
                ], 404);
            }

            $signaturePath = $contact->signature;

            if (!$signaturePath || trim($signaturePath) === '') {
                return response()->json([
                    'success' => false,
                    'message' => 'No signature available for this contact'
                ], 404);
            }

            // Build full URL if path is relative
            $signatureUrl = $signaturePath;
            if (!filter_var($signaturePath, FILTER_VALIDATE_URL)) {
                $signatureUrl = asset('storage/' . $signaturePath);
            }

            return response()->json([
                'success' => true,
                'signature_path' => $signaturePath,
                'signature_url' => $signatureUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching contact signature: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch contact signature: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveContactSignature(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Ensure user is authenticated
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $request->validate([
                'contact_id' => 'required|exists:crm_customer_contacts,id',
                'signature' => 'required|string', // Base64 image data
            ]);

            $contact = \App\Models\CRM\CustomerContact::findOrFail($request->contact_id);
            
            // Decode base64 image
            $signatureData = $request->signature;
            
            // Check if it's a data URL
            if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $matches)) {
                $imageData = substr($signatureData, strpos($signatureData, ',') + 1);
                $imageData = base64_decode($imageData);
                $extension = $matches[1];
                
                // Generate unique filename
                $filename = 'contact_' . $contact->id . '_' . time() . '.' . $extension;
                $path = 'signatures/' . $filename;
                
                // Save to storage
                \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
                
                // Delete old signature if exists
                if ($contact->signature && \Illuminate\Support\Facades\Storage::disk('public')->exists($contact->signature)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($contact->signature);
                }
                
                // Update contact
                $contact->signature = $path;
                $contact->save();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Signature saved successfully',
                    'signature_path' => $path,
                    'signature_url' => asset('storage/' . $path)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature format'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error saving contact signature: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save contact signature: ' . $e->getMessage()
            ], 500);
        }
    }

}