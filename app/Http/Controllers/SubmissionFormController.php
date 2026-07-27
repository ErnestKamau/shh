<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormTemplateType;
use App\Models\SubmissionFormPermission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Services\PageLayoutRegistry;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;

class SubmissionFormController extends Controller
{
    /**
     * Display a listing of submission forms (Livewire-backed registry UI).
     */
    public function index(): View
    {
        return view('submission-forms.index');
    }

    /**
     * Show the form for creating a new submission form
     * 
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        $labSections = \App\SampleAnalysisStage::where('active', 1)
            ->where('is_sample_stage', 0)
            ->orderBy('name')
            ->get();
        $availablePages = $this->getCuratedPlacementPages();
        $templateFormTypes = SubmissionFormTemplateType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $templateForms = SubmissionForm::query()
            ->where('form_type', 'template')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        $customers = \App\Models\CRM\CRMCustomer::orderBy('name')->get(['id', 'name']);
        $sampleTypes = \App\SampleType::where('active', true)->orderBy('name')->get(['id', 'name']);

        $fromRft = $request->query('from') === 'rft' || $request->boolean('trf');
        $trfDefaults = $fromRft ? [
            'name' => 'Test Request Form',
            'document_code' => 'TRF-',
            'description' => 'Test request form template linked to one or more sample types.',
            'naming_convention_prefix' => 'TRF',
            'naming_convention_format' => '{prefix}-{year}-{sequence}',
            'is_customer_portal_form' => true,
            'form_type' => 'template',
            'placement_mode' => 'page_section',
            'placement_slot' => ['customer_portal', 'admin_portal', 'samples_receiving'],
            'version' => '1.0',
            'issue_date' => now()->toDateString(),
        ] : [];

        return view('submission-forms.create', compact(
            'labSections',
            'availablePages',
            'templateFormTypes',
            'templateForms',
            'customers',
            'sampleTypes',
            'fromRft',
            'trfDefaults'
        ));
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
            'is_customer_request_form' => ['boolean'],
            'lims_destination_pages' => ['nullable', 'array'],
            'lims_destination_pages.*' => ['string', Rule::in($availablePageNames)],
            'start_submission_number' => ['nullable', 'integer', 'min:1'],
            'version' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'print_template_name' => ['nullable', 'string', 'max:255'],
            'template_form_type_id' => [
                'nullable',
                'integer',
                'exists:submission_form_template_types,id',
            ],
            'target_pages' => ['nullable', 'array'],
            'target_pages.*' => ['string', Rule::in($availablePageNames)],
            'placement_mode' => ['required', Rule::in(['button_trigger', 'page_section'])],
            'display_mode' => ['nullable', Rule::in(['expanded', 'collapsible'])],
            'placement_slot' => ['nullable', 'array'],
            'trigger_button_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids.*' => ['exists:sample_analysis_stages,id'],
            'form_type' => ['required', 'string', Rule::in(['template', 'attachment'])],
            'template_form_ids' => ['nullable', 'array'],
            'template_form_ids.*' => ['uuid', 'exists:submission_forms,id'],
        ]);

        $validated['target_pages'] = array_values($validated['target_pages'] ?? []);
        $validated['display_mode'] = $validated['display_mode'] ?? 'expanded';
        $validated['is_customer_portal_form'] = $request->boolean('is_customer_portal_form');
        $validated['is_customer_request_form'] = $request->boolean('is_customer_request_form');
        if ($validated['is_customer_request_form'] && ! $validated['is_customer_portal_form']) {
            $validated['is_customer_portal_form'] = true;
        }
        if (! $validated['is_customer_portal_form']) {
            $validated['is_customer_request_form'] = false;
        }
        $validated['template_form_type_id'] = isset($validated['template_form_type_id']) ? (int) $validated['template_form_type_id'] : null;
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

        if ($this->submissionFormCustomersPivotExists()) {
            $form->customers()->sync($request->input('customer_ids', []));
        } else {
            Log::warning('Skipping submission form customer sync because pivot table is missing.', [
                'table' => 'submission_form_customers',
                'submission_form_id' => $form->id,
            ]);
        }

        if ($this->submissionFormSampleTypesPivotExists()) {
            $form->sampleTypes()->sync($request->input('sample_type_ids', []));
        } else {
            Log::warning('Skipping submission form sample type sync because pivot table is missing.', [
                'table' => 'submission_form_sample_types',
                'submission_form_id' => $form->id,
            ]);
        }

        if ($validated['form_type'] === 'attachment' && Schema::hasTable('submission_form_template_links')) {
            $form->templateForms()->sync($request->input('template_form_ids', []));
        }

        $this->bustSubmissionFormPageCache($validated['target_pages'] ?? []);

        if ($request->input('return_to') === 'rft') {
            return redirect()
                ->route('submission-forms.builder', $form)
                ->with('success', 'TRF created. Add sections and fields, then link sample types on Edit Form.');
        }

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

        $sections = app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm);
        $submissionForm->setRelation('sections', $sections);

        $statistics = $submissionForm->getStatistics();
        $statistics['total_sections'] = $sections->count();
        $statistics['total_elements'] = $sections
            ->sum(fn ($section) => $section->elementHolders->sum(fn ($holder) => $holder->elements->count()));

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

        $availablePages = $this->getCuratedPlacementPages(
            array_merge(
                $submissionForm->target_pages ?? [],
                $submissionForm->lims_destination_pages ?? []
            )
        );
        $templateFormTypes = SubmissionFormTemplateType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $templateForms = SubmissionForm::query()
            ->where('form_type', 'template')
            ->where('is_active', true)
            ->where('id', '!=', $submissionForm->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        $customers = \App\Models\CRM\CRMCustomer::orderBy('name')->get(['id', 'name']);
        $sampleTypes = \App\SampleType::where('active', true)->orderBy('name')->get(['id', 'name']);
        $customersPivotExists = $this->submissionFormCustomersPivotExists();
        $sampleTypesPivotExists = $this->submissionFormSampleTypesPivotExists();

        $relationsToLoad = ['sampleAnalysisStages'];

        if ($customersPivotExists) {
            $relationsToLoad[] = 'customers';
        }

        if ($sampleTypesPivotExists) {
            $relationsToLoad[] = 'sampleTypes';
        }

        if ($submissionForm->form_type === 'attachment' && Schema::hasTable('submission_form_template_links')) {
            $relationsToLoad[] = 'templateForms';
        }

        $submissionForm->load($relationsToLoad);

        if (! $customersPivotExists) {
            $submissionForm->setRelation('customers', collect());
        }

        if (! $sampleTypesPivotExists) {
            $submissionForm->setRelation('sampleTypes', collect());
        }

        return view('submission-forms.edit', compact('submissionForm', 'labSections', 'availablePages', 'templateFormTypes', 'templateForms', 'customers', 'sampleTypes'));
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
            'is_customer_request_form' => ['boolean'],
            'lims_destination_pages' => ['nullable', 'array'],
            'lims_destination_pages.*' => ['string', Rule::in($availablePageNames)],
            'start_submission_number' => ['nullable', 'integer', 'min:1'],
            'version' => ['required', 'string', 'max:50'],
            'issue_date' => ['required', 'date'],
            'print_template_name' => ['nullable', 'string', 'max:255'],
            'template_form_type_id' => [
                'nullable',
                'integer',
                'exists:submission_form_template_types,id',
            ],
            'target_pages' => ['nullable', 'array'],
            'target_pages.*' => ['string', Rule::in($availablePageNames)],
            'placement_mode' => ['required', Rule::in(['button_trigger', 'page_section'])],
            'display_mode' => ['nullable', Rule::in(['expanded', 'collapsible'])],
            'placement_slot' => ['nullable', 'array'],
            'trigger_button_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids' => ['nullable', 'array'],
            'sample_analysis_stage_ids.*' => ['exists:sample_analysis_stages,id'],
            'form_type' => ['required', 'string', Rule::in(['template', 'attachment'])],
            'template_form_ids' => ['nullable', 'array'],
            'template_form_ids.*' => ['uuid', 'exists:submission_forms,id'],
        ]);

        $validated['target_pages'] = array_values($validated['target_pages'] ?? []);
        $validated['display_mode'] = $validated['display_mode'] ?? 'expanded';
        $validated['is_customer_portal_form'] = $request->boolean('is_customer_portal_form');
        $validated['is_customer_request_form'] = $request->boolean('is_customer_request_form');
        if ($validated['is_customer_request_form'] && ! $validated['is_customer_portal_form']) {
            $validated['is_customer_portal_form'] = true;
        }
        if (! $validated['is_customer_portal_form']) {
            $validated['is_customer_request_form'] = false;
        }
        $validated['template_form_type_id'] = isset($validated['template_form_type_id']) ? (int) $validated['template_form_type_id'] : null;
        $validated['lims_destination_pages'] = $validated['is_customer_portal_form']
            ? array_values($validated['lims_destination_pages'] ?? ['sample-workflow'])
            : [];

        $submissionForm->update($validated);

        $this->bustSubmissionFormPageCache($validated['target_pages'] ?? []);

        if ($this->submissionFormCustomersPivotExists()) {
            $submissionForm->customers()->sync($request->input('customer_ids', []));
        } else {
            Log::warning('Skipping submission form customer sync because pivot table is missing.', [
                'table' => 'submission_form_customers',
                'submission_form_id' => $submissionForm->id,
            ]);
        }

        if ($this->submissionFormSampleTypesPivotExists()) {
            $submissionForm->sampleTypes()->sync($request->input('sample_type_ids', []));
        } else {
            Log::warning('Skipping submission form sample type sync because pivot table is missing.', [
                'table' => 'submission_form_sample_types',
                'submission_form_id' => $submissionForm->id,
            ]);
        }

        if ($validated['form_type'] === 'attachment' && Schema::hasTable('submission_form_template_links')) {
            $submissionForm->templateForms()->sync($request->input('template_form_ids', []));
        }

        if (isset($validated['sample_analysis_stage_ids'])) {
            $submissionForm->sampleAnalysisStages()->sync($validated['sample_analysis_stage_ids']);
        } else {
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
            ->flatMap(function ($route) {
                $name = (string) $route->getName();
                $uri = '/' . ltrim((string) $route->uri(), '/');

                $pages = [[
                    'value' => $name,
                    'label' => $name . ' (' . $uri . ')',
                    'name' => $name,
                    'uri' => $uri,
                ]];

                if (in_array($name, ['sample-workflow', 'sample-workflow-stage'], true)) {
                    foreach ($this->getSampleWorkflowStatusesForPlacement() as $status) {
                        $value = $name . '@status=' . $status;
                        $pages[] = [
                            'value' => $value,
                            'label' => $name . ' [' . $status . '] (' . rtrim($uri, '/') . '/' . rawurlencode($status) . ')',
                            'name' => $value,
                            'uri' => rtrim($uri, '/') . '/' . rawurlencode($status),
                        ];
                    }
                }

                return $pages;
            })
            ->unique('value')
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Curated placement / LIMS destination options for form admin UI.
     * Always includes any already-selected values so edits do not drop them.
     *
     * @param  array<int, string>  $alwaysInclude
     * @return array<int, array{value: string, label: string, name: string, uri: string}>
     */
    private function getCuratedPlacementPages(array $alwaysInclude = []): array
    {
        $allowedExact = [
            'sample-workflow',
            'sample-workflow-stage',
            'sample-workflow.request-for-testing',
            'sample-workflow.request-for-testing.fill',
            'sample-submission-requests.index',
            'sample-submission-requests.create',
            'sample-submission-requests.show',
            'dashboard-lab',
            'lab-home',
            'system-planner.dashboard',
            'system-planner.fill-sampling-forms',
            'submission-forms.index',
            'submission-forms.instances.index',
        ];

        $allowedPrefixes = [
            'sample-workflow',
            'sample-submission',
            'submission-forms.instances',
            'system-planner',
            'dashboard-lab',
        ];

        $alwaysInclude = collect($alwaysInclude)
            ->filter(fn ($value) => is_string($value) && $value !== '')
            ->unique()
            ->values()
            ->all();

        return collect($this->getAvailablePlacementPages())
            ->filter(function (array $page) use ($allowedExact, $allowedPrefixes, $alwaysInclude): bool {
                $value = (string) ($page['value'] ?? '');

                if (in_array($value, $alwaysInclude, true) || in_array($value, $allowedExact, true)) {
                    return true;
                }

                foreach ($allowedPrefixes as $prefix) {
                    if (str_starts_with($value, $prefix)) {
                        return true;
                    }
                }

                return false;
            })
            ->unique('value')
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * Get sample workflow status labels for context-aware page placement.
     *
     * @return array<int, string>
     */
    private function getSampleWorkflowStatusesForPlacement(): array
    {
        if (!function_exists('getSampleWorflowStages')) {
            return [];
        }

        return collect((array) getSampleWorflowStages())
            ->filter(fn($stage) => is_string($stage) && trim($stage) !== '' && trim($stage) !== 'All Samples')
            ->map(fn($stage) => trim($stage))
            ->unique()
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
     * Store a new template form type and return it for immediate dropdown use.
     */
    public function storeTemplateFormType(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $normalizedName = trim((string) preg_replace('/\s+/', ' ', $validated['name']));

        if ($normalizedName === '') {
            return response()->json([
                'success' => false,
                'message' => 'Template form type name is required.',
            ], 422);
        }

        $existingTemplateType = SubmissionFormTemplateType::query()
            ->where('name', $normalizedName)
            ->first();

        if ($existingTemplateType) {
            if (! $existingTemplateType->is_active) {
                $existingTemplateType->is_active = true;
                $existingTemplateType->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Template form type already exists.',
                'templateFormType' => [
                    'id' => (int) $existingTemplateType->id,
                    'name' => $existingTemplateType->name,
                ],
            ]);
        }

        $templateType = SubmissionFormTemplateType::create([
            'name' => $normalizedName,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template form type created successfully.',
            'templateFormType' => [
                'id' => (int) $templateType->id,
                'name' => $templateType->name,
            ],
        ], 201);
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
            ? $this->getAvailablePlacementPageNames()
            : $targetPages;

        foreach ($routesToClear as $routeName) {
            \Illuminate\Support\Facades\Cache::forget($this->getSubmissionFormPageCacheKey($routeName));

            if (is_string($routeName) && str_contains($routeName, '@status=')) {
                $baseRouteName = explode('@status=', $routeName, 2)[0];
                \Illuminate\Support\Facades\Cache::forget($this->getSubmissionFormPageCacheKey($baseRouteName));
            }
        }
    }

    private function getSubmissionFormPageCacheKey(string $routeName): string
    {
        return 'sf_page_forms_' . md5($routeName);
    }

    private function submissionFormCustomersPivotExists(): bool
    {
        return Schema::hasTable('submission_form_customers');
    }

    private function submissionFormSampleTypesPivotExists(): bool
    {
        return Schema::hasTable('submission_form_sample_types');
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

        $sections = app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm);
        $submissionForm->setRelation('sections', $sections);

        $existingValues = collect();

        return view('submission-forms.preview', compact('submissionForm', 'existingValues'));
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
        $validTypes = ['client_select', 'sample_type_select', 'client_unit_select', 'client_contact_select', 'client_submission_officers_select', 'analysis_type_select', 'analysis_elements_select', 'store_select', 'store_slot_select', 'sample_condition_select', 'standard_select', 'sample_point_select', 'user_select'];
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
                    $analysisTypesQuery = \App\AnalysisType::where('sample_type_id', $sampleTypeId)
                        ->where('active', 1);

                    $companyId = function_exists('getUserCompany') ? getUserCompany() : null;
                    if ($companyId) {
                        $analysisTypesQuery->where(function ($query) use ($companyId): void {
                            $query->where('company_id', $companyId)
                                ->orWhereNull('company_id');
                        });
                    }

                    $analysisTypes = $analysisTypesQuery->orderBy('name')->get();

                    foreach ($analysisTypes as $analysisType) {
                        $options[] = [
                            'value' => $analysisType->id,
                            'label' => $analysisType->name . ' (' . $analysisType->code . ')'
                        ];
                    }
                }
                break;

            case 'analysis_elements_select':
                $analysisTypeId = $request->get('analysis_type_id');
                if ($analysisTypeId) {
                    $elements = \App\AnalysisElements::where('analysis_type_id', $analysisTypeId)
                        ->where('active', 1)
                        ->with('analyte')
                        ->get();

                    foreach ($elements as $element) {
                        $parameterName = 'Unknown Parameter';
                        if ($element->analyte) {
                            $parameterName = $element->analyte->name ?? 'Unknown Parameter';
                        } elseif ($element->analyte_id) {
                            $analyte = \App\Analyte::find($element->analyte_id);
                            $parameterName = $analyte ? $analyte->name : 'Unknown Parameter';
                        }

                        $methodName = 'No Method';
                        if ($element->method) {
                            $method = \App\AnalysisMethod::find($element->method);
                            $methodName = $method ? $method->name : $element->method;
                        }

                        $options[] = [
                            'value' => $element->id,
                            'label' => $parameterName . ' (' . $methodName . ')'
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