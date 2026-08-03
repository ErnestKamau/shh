<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormElement;
use App\Models\SampleSubmissionRequest;
use App\Directorate;
use App\SampleHeader;
use App\SampleDetails;
use App\Services\Sampleworkflow\TestRequestFormPdfService;
use App\Services\SubmissionForm\SubmissionFormSchemaHelper;
use App\Services\SubmissionForm\SubmissionFormSubmissionService;
use App\Services\SubmissionFormBatchSyncService;
use App\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class FormInstanceController extends Controller
{
    /**
     * Display a listing of form instances (all users can see all forms)
     */
    public function index(Request $request)
    {
        $query = SubmissionFormInstance::with(['submissionForm', 'submittedBy'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->withPriority($request->priority);
        }

        // Search by form number or title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('form_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('submissionForm', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $instances = $query->paginate(15);

        return view('submission-forms.instances.index', compact('instances'));
    }

    /**
     * Show the form for creating a new form instance
     */
    public function create(SubmissionForm $submissionForm)
    {
        if ($submissionForm->is_customer_portal_form && ! $submissionForm->isLimsFillable() && request()->routeIs('submission-forms.instances.create')) {
            return redirect()->route('submission-forms.index')
                ->with('error', 'This form is configured for customer portal submissions only.');
        }

        // Check if user can create instances for this form
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is not available for submission.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        return view('submission-forms.instances.create', compact('submissionForm'));
    }

    /**
     * Store a newly created form instance
     */
    public function store(Request $request, SubmissionForm $submissionForm)
    {
        // Check if user can create instances for this form
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is not available for submission.');
        }

        if ($submissionForm->is_customer_portal_form && ! $submissionForm->isLimsFillable() && auth()->check()) {
            return redirect()->back()->with('error', 'This form can only be submitted from the customer portal.');
        }

        // Determine if this is a public submission
        $isPublicSubmission = !Auth::check();
        $submittedBy = $isPublicSubmission ? null : Auth::id();

        // Generate form number and create instance in a transaction with retry logic
        Log::info('Creating form instance', [
            'form_id' => $submissionForm->id,
            'form_name' => $submissionForm->name,
            'is_public' => $isPublicSubmission
        ]);

        $instance = $this->createFormInstanceWithRetry($submissionForm, $request, $submittedBy);

        // Log the creation (only if user is authenticated)
        if (!$isPublicSubmission) {
            $instance->logAction('created', Auth::user());
        }

        // Redirect based on submission type
        if ($isPublicSubmission) {
            return redirect()->route('forms.show', $submissionForm)
                ->with('success', 'Form instance created successfully. You can now fill out the form.');
        } else {
            return redirect()->route('submission-forms.instances.fill', [
                'submissionForm' => $submissionForm,
                'instance' => $instance
            ])->with('success', 'Form instance created successfully. You can now fill out the form.');
        }
    }

    /**
     * Launch form filling inline (no navigation to create-instance page).
     * Creates a draft instance and returns the fill URL as JSON.
     */
    public function launchInline(Request $request, SubmissionForm $submissionForm)
    {
        if (!$submissionForm->isPublishedAndActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This form is not available for submission.',
            ], 422);
        }

        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $userId = auth()->id();
        $finalizedStatuses = ['submitted', 'approved', 'rejected', 'completed', 'cancelled', 'void'];
        $buildLaunchResponse = static function (SubmissionFormInstance $instance, bool $reused) use ($submissionForm) {
            return response()->json([
                'success' => true,
                'instance_id' => $instance->id,
                'reused' => $reused,
                'fill_url' => route('submission-forms.instances.fill-sample', [
                    'submissionForm' => $submissionForm,
                    'instance' => $instance,
                ]),
            ]);
        };

        $reuseExisting = $request->boolean('reuse_existing', true);
        if ($reuseExisting) {
            $requestedInstanceId = trim((string) $request->input('existing_instance_id', ''));
            $submissionFormId = (string) $submissionForm->id;

            if ($requestedInstanceId !== '' && Str::isUuid($requestedInstanceId) && Str::isUuid($submissionFormId)) {
                $requestedInstance = SubmissionFormInstance::query()
                    ->where('id', $requestedInstanceId)
                    ->where('submission_form_id', $submissionFormId)
                    ->where('submitted_by', $userId)
                    ->where(function ($query) use ($finalizedStatuses) {
                        $query->whereNull('status')
                              ->orWhereNotIn('status', $finalizedStatuses);
                    })
                    ->first();

                if ($requestedInstance) {
                    return $buildLaunchResponse($requestedInstance, true);
                }
            }

            $existingActiveInstance = SubmissionFormInstance::query()
                ->where('submission_form_id', $submissionForm->id)
                ->where('submitted_by', $userId)
                ->where(function ($query) use ($finalizedStatuses) {
                    $query->whereNull('status')
                          ->orWhereNotIn('status', $finalizedStatuses);
                })
                ->latest('updated_at')
                ->first();

            if ($existingActiveInstance) {
                return $buildLaunchResponse($existingActiveInstance, true);
            }
        }

        $title = trim((string) $request->input('title', ''));
        if ($title === '') {
            $title = $submissionForm->name . ' - ' . now()->format('Y-m-d H:i');
        }

        $instance = SubmissionFormInstance::create([
            'submission_form_id' => $submissionForm->id,
            'form_number' => null,
            'sequence_number' => null,
            'title' => $title,
            'submitted_by' => $userId,
            'status' => 'draft',
            'priority' => 'normal',
            'due_date' => null,
        ]);

        $instance->logAction('created', auth()->user());

        return $buildLaunchResponse($instance, false);
    }

    /**
     * Display the form for filling out
     */
    protected function canAccessForms(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }
        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return true;
        }
        // Spatie permission check
        if ($user->can('laboratory.components.rft form.view') || $user->can('laboratory.permission')) {
            return true;
        }

        return false;
    }

    protected function assertLimsCanFillForm(SubmissionForm $submissionForm): void
    {
        if ($submissionForm->is_customer_portal_form && ! $submissionForm->isLimsFillable()) {
            abort(403, 'This form can only be filled from the customer portal.');
        }
    }

    public function fill(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        $this->assertLimsCanFillForm($submissionForm);

        // Allow access if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to access this form instance.');
        }

        // Check if form is still available
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is no longer available for submission.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sampleAnalysisStages'
        ]);
        $submissionForm->setRelation(
            'sections',
            app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm)
        );

        // Filter sample types based on lab sections
        $allowedSampleTypeIds = null;
        if ($submissionForm->sampleAnalysisStages->isNotEmpty()) {
            $stageIds = $submissionForm->sampleAnalysisStages->pluck('id')->toArray();
            $allowedSampleTypeIds = \App\SampleType::whereHas('sampleAnalysisStages', function ($q) use ($stageIds) {
                $q->whereIn('sample_analysis_stages.id', $stageIds);
            })->pluck('id')->toArray();
        }

        // Load existing values
        $existingValues = $instance->values()->with('element')->get();
        $inline = request()->boolean('inline') || request()->header('Sec-Fetch-Dest') === 'iframe';
        $use_lab_layout = $inline ? 0 : 1;

        return view('submission-forms.instances.fill-sample', compact('submissionForm', 'instance', 'existingValues', 'use_lab_layout', 'allowedSampleTypeIds', 'inline'));

        // return view('submission-forms.instances.fill', compact('submissionForm', 'instance', 'existingValues'));
    }

    /**
     * Display the form for filling out (sample-submissions layout)
     */
    public function fillSample(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        $this->assertLimsCanFillForm($submissionForm);

        // Allow access if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to access this form instance.');
        }

        // Check if form is still available
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is no longer available for submission.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'sampleAnalysisStages'
        ]);
        $submissionForm->setRelation(
            'sections',
            app(SubmissionFormSchemaHelper::class)->uniqueSections($submissionForm)
        );

        // Filter sample types based on lab sections
        $allowedSampleTypeIds = null;
        if ($submissionForm->sampleAnalysisStages->isNotEmpty()) {
            $stageIds = $submissionForm->sampleAnalysisStages->pluck('id')->toArray();
            $allowedSampleTypeIds = \App\SampleType::whereHas('sampleAnalysisStages', function ($q) use ($stageIds) {
                $q->whereIn('sample_analysis_stages.id', $stageIds);
            })->pluck('id')->toArray();
        }

        // Load existing values
        $existingValues = $instance->values()->with('element')->get();

        $inline = request()->boolean('inline') || request()->header('Sec-Fetch-Dest') === 'iframe';

        return view('submission-forms.instances.fill-sample', compact('submissionForm', 'instance', 'existingValues', 'allowedSampleTypeIds', 'inline'));
    }

    /**
     * Update the form instance with submitted data
     */
    public function update(
        Request $request,
        SubmissionForm $submissionForm,
        SubmissionFormInstance $instance,
        SubmissionFormSubmissionService $submissionService
    )
    {
        $this->assertLimsCanFillForm($submissionForm);

        $user = auth()->user();

        // Allow update if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to update this form instance.');
        }


        if (! $instance->isDraft()) {
            return redirect()->back()->with('error', 'Only draft form instances can be updated.');
        }

        $action = (string) $request->input('action');
        if (! in_array($action, ['draft', 'submit'], true)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Choose Save as Draft or Submit Form.');
        }

        // Load form elements for validation (exclude builder-hidden sections/elements)
        $elements = SubmissionFormElement::whereHas('holder.section', function ($query) use ($submissionForm) {
            $query->where('submission_form_id', $submissionForm->id)
                ->where(function ($sectionQuery) {
                    $sectionQuery->where('is_hidden', false)->orWhereNull('is_hidden');
                });
        })
            ->where(function ($elementQuery) {
                $elementQuery->where('is_hidden', false)->orWhereNull('is_hidden');
            })
            ->with('holder.section')
            ->get();


        // Build validation rules
        $validationRules = $submissionService->buildValidationRules(
            $elements,
            $request,
            requireRequired: $action === 'submit'
        );

        // dd($validationRules);

        // Validate the request
        $validator = Validator::make($request->all(), $validationRules);

        // dd($validator->validate());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        DB::beginTransaction();
        try {
            $submissionService->processFormData($instance, $request, $elements);
            Log::info('Processing form data');

            if ($action === 'submit') {
                if (empty($instance->form_number)) {
                    $this->assignFormNumberWithRetry($instance, $submissionForm);
                    $instance->refresh();
                }

                $instance->submit(Auth::user());
            }

            DB::commit();

            if ($action === 'submit') {
                try {
                    app(\App\Services\Commercial\CommercialEnquiryFromFormService::class)
                        ->syncFromSubmittedInstance($instance->fresh(['values.element', 'submissionForm', 'crmCustomer']));
                } catch (\Throwable $th) {
                    Log::warning('Commercial enquiry sync failed after LSR form submit.', [
                        'instance_id' => $instance->id,
                        'message' => $th->getMessage(),
                    ]);
                }
            }

            if ($action === 'draft') {
                return redirect()->back()->with('success', 'Draft saved successfully.');
            }

            $labIntakeCaseServiceClass = 'App\\Services\\LabIntakeCaseService';
            if (class_exists($labIntakeCaseServiceClass)) {
                try {
                    // Sync the intake workflow once the submission is durable.
                    app($labIntakeCaseServiceClass)->syncFromSubmission($instance, Auth::user());
                } catch (\Throwable $th) {
                    Log::warning('Lab intake case sync failed after form submit.', [
                        'instance_id' => $instance->id,
                        'message' => $th->getMessage(),
                    ]);
                }
            }

            return redirect()->route('submission-forms.instances.show', [
                'submissionForm' => $submissionForm,
                'instance' => $instance
            ])->with('success', 'Form submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating form instance: ' . $e->getMessage());
            //dd($e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the form. Please try again.');
        }
    }

    /**
     * Push current submission form field values onto all linked sample headers and unprocessed staging rows.
     */
    public function applyToBatches(int $instance, SubmissionFormBatchSyncService $syncService): \Illuminate\Http\RedirectResponse
    {
        if (! $this->canAccessForms()) {
            abort(403, 'You are not allowed to update linked batches from this form.');
        }

        $instanceModel = SubmissionFormInstance::query()->findOrFail($instance);

        if (! $instanceModel->batches()->exists()) {
            return redirect()->back()->with('error', 'No lab batches are linked to this submission yet.');
        }

        $result = [
            'updated' => 0,
            'skipped' => 0,
            'messages' => ['success' => [], 'warning' => [], 'error' => []],
        ];

        try {
            DB::transaction(function () use ($syncService, $instanceModel, &$result): void {
                $result = $syncService->sync($instanceModel);
            });
        } catch (\Throwable $e) {
            Log::error('applyToBatches failed', [
                'instance_id' => $instance,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Could not update batches from the form. Please try again or contact IT.');
        }

        $flashParts = [sprintf('%d linked batch(es) were updated from the submission form.', $result['updated'])];
        if ($result['skipped'] > 0) {
            $flashParts[] = sprintf('%d batch(es) were not changed (the form could not be matched to them).', $result['skipped']);
        }
        $flash = implode(' ', $flashParts);

        if ($result['messages']['error'] !== []) {
            return redirect()->back()
                ->with('error', implode(' ', $result['messages']['error']));
        }

        return redirect()->back()
            ->with('success', $flash)
            ->with('apply_batches_warnings', $result['messages']['warning']);
    }

    /**
     * Sample Integrity & Acceptance Check page for a submitted commercial request.
     */
    public function sampleIntegrityCheck(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to view this form instance.');
        }

        if ((string) $instance->submission_form_id !== (string) $submissionForm->id) {
            abort(404);
        }

        return view('submission-forms.instances.sample-integrity-check', compact('submissionForm', 'instance'));
    }

    /**
     * Display the specified form instance
     */
    public function show(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Allow view if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to view this form instance.');
        }

        $submissionForm->load(['sections.elementHolders.elements']);

        $instance->load(['batches', 'analysisAcceptanceForms']);

        if (Auth::user()->is_tablet == 1) {
            $existingValues = $instance->values()->with('element')->get();
            $auditLogs = $instance->auditLogs()->with('user')->latest()->get();
            $linkedBatchesOutOfSyncWithForm = false;
            if ($instance->batches()->exists()) {
                $linkedBatchesOutOfSyncWithForm = app(SubmissionFormBatchSyncService::class)
                    ->linkedBatchesOutOfSyncWithForm($instance);
            }
            $intakeCase = method_exists($instance, 'labIntakeCase') ? $instance->labIntakeCase : null;
            /** @var \App\User|null $currentUser */
            $currentUser = Auth::user();
            $canManageIntake = $currentUser && ($currentUser->hasRole('Sample Reception') || $currentUser->hasRole('admin'));
            $zones = collect();
            $directorates = collect();
            $isLabIntakeSubmission = method_exists($instance, 'isLabIntakeSubmission')
                ? (bool) $instance->isLabIntakeSubmission()
                : false;
            if ($isLabIntakeSubmission && $canManageIntake) {
                $zones = Zone::query()->orderBy('key')->get();
                $directorates = Directorate::query()->where('active', 1)->orderBy('name')->get();
            }

            return view('submission-forms.instances.show-tablet', compact(
                'submissionForm',
                'instance',
                'existingValues',
                'auditLogs',
                'linkedBatchesOutOfSyncWithForm',
                'intakeCase',
                'canManageIntake',
                'zones',
                'directorates'
            ));
        }

        return view('submission-forms.instances.show', compact('submissionForm', 'instance'));
    }

    /**
     * Print the specified form instance
     */
    public function print(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Allow print if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to print this form instance.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);


        $existingValues = $instance->values()->with('element')->get();

        // Load existing values
        $existingValues = $instance->getSubmittedFormData();

        // return json_encode($existingValues);

        // return json_encode($existingValues);

        // Get the print template name from the form
        $templateName = $submissionForm->getPrintTemplateName();

        // Check if the template exists, fallback to default if not
        if (!view()->exists($templateName)) {
            $templateName = 'submission-forms.print.default';
        }

        $logoUrl = '';
        $company = getActiveCompany();
        if ($company && ! empty($company->logo)) {
            $logoUrl = str_starts_with($company->logo, 'http') ? $company->logo : url($company->logo);
        }

        return view($templateName, compact('submissionForm', 'instance', 'existingValues', 'logoUrl'));
    }

    /**
     * Show the form for editing a draft instance
     */
    public function edit(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        $user = auth()->user();

        // Allow edit if user has RFT Form permission or is admin
        if (! $this->canAccessForms()) {
            abort(403, 'You are not authorized to edit this form instance.');
        }

        // Check if instance can be edited
        if (!$instance->isDraft()) {
            return redirect()->back()->with('error', 'This form instance cannot be edited.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Load existing values
        $existingValues = $instance->values()->with('element')->get();

        return view('submission-forms.instances.edit', compact('submissionForm', 'instance', 'existingValues'));
    }

    /**
     * Delete a draft instance
     */
    public function destroy(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Allow deletion by any authenticated user (removed ownership restriction)
        // Users can now delete all submission forms, even ones they didn't create

        DB::beginTransaction();

        try {
            // Delete all related batches and their samples
            $instance->batches()->get()->each(function (\App\SampleHeader $batch) {
                // Delete samples linked to this batch
                if ($batch->samples()->exists()) {
                    $batch->samples()->delete();
                }

                // Delete any staging details linked to this batch
                if (method_exists($batch, 'stagingDetails') && $batch->stagingDetails()->exists()) {
                    $batch->stagingDetails()->delete();
                }

                // Finally delete the batch itself
                $batch->delete();
            });

            // Delete instance values and audit logs
            $instance->values()->delete();
            $instance->auditLogs()->delete();

            // Delete the instance
            $instance->delete();

            DB::commit();

            return redirect()->route('sample-workflow', ['status' => 'Samples Reception'])
                ->with('success', 'Form submission and all related batches and samples deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting form instance and related records', [
                'instance_id' => $instance->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'An error occurred while deleting the submission. Please try again.');
        }
    }

    /**
     * Generate a unique form number with automatic increment on duplicates
     */
    private function generateFormNumber(SubmissionForm $form): array
    {
        return \App\Services\FormNumberGenerator::generate($form);
    }

    /**
     * Create form instance without generating number (will be generated on first save)
     */
    private function createFormInstanceWithRetry(SubmissionForm $submissionForm, Request $request, $submittedBy): SubmissionFormInstance
    {
        return SubmissionFormInstance::create([
            'submission_form_id' => $submissionForm->id,
            'form_number' => null,
            'sequence_number' => null,
            'title' => $request->input('title'),
            'submitted_by' => $submittedBy,
            'status' => 'draft',
            'priority' => $request->input('priority', 'normal'),
            'due_date' => $request->input('due_date')
        ]);
    }

    /**
     * Assign form number to an existing instance with retry logic
     */
    private function assignFormNumberWithRetry(SubmissionFormInstance $instance, SubmissionForm $submissionForm): void
    {
        $maxRetries = 5;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            $retryCount++;

            try {
                DB::transaction(function () use ($instance, $submissionForm) {
                    // Generate form number
                    $formNumber = $this->generateFormNumber($submissionForm);

                    // Update the instance
                    $instance->update([
                        'form_number' => $formNumber['format'],
                        'sequence_number' => $formNumber['sequence_no'],
                    ]);
                });

                // Success – exit the method
                return;
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle unique violations on both MySQL (23000) and PostgreSQL (23505)
                $isDuplicateFormNumber = in_array((string) $e->getCode(), ['23000', '23505'], true)
                    && strpos($e->getMessage(), 'submission_form_instances_form_number_unique') !== false;

                if ($isDuplicateFormNumber) {
                    Log::warning('Duplicate form number detected during assignment, retrying', [
                        'instance_id' => $instance->id,
                        'form_id' => $submissionForm->id,
                        'retry_count' => $retryCount,
                        'error' => $e->getMessage()
                    ]);

                    if ($retryCount >= $maxRetries) {
                        Log::error('Maximum retries reached for form instance number assignment', [
                            'instance_id' => $instance->id,
                            'form_id' => $submissionForm->id,
                            'retries' => $maxRetries
                        ]);

                        throw new \Exception('Failed to assign form number after maximum retries', 0, $e);
                    }

                    // Small delay before retry to reduce collision probability
                    usleep(100000); // 100ms delay
                    continue;
                }

                // If it's not a duplicate key error, re-throw
                throw $e;
            }
        }
    }

    /**
     * Build validation rules for form elements
     */
    private function buildValidationRules($elements, Request $request): array
    {
        $rules = [];

        foreach ($elements as $element) {
            $fieldName = $element->name;
            $elementRules = [];

            if (\App\Services\SubmissionForm\SubmissionFormSchemaHelper::isBuilderHiddenElement($element)) {
                $rules[$fieldName] = ['nullable'];
                continue;
            }

            // Required validation
            if ($element->is_required) {
                $elementRules[] = 'required';
            } else {
                $elementRules[] = 'nullable';
            }

            // Type-specific validation
            switch ($element->element_type) {
                case 'email':
                    $elementRules[] = 'email';
                    break;
                case 'number':
                    $elementRules[] = 'numeric';
                    break;
                case 'date':
                    $elementRules[] = 'date';
                    break;
                case 'datetime':
                    $elementRules[] = 'date';
                    break;
                case 'file':
                    $elementRules[] = 'file';
                    break;
                case 'camera_photo':
                case 'image_upload':
                    $elementRules[] = 'image';
                    $elementRules[] = 'mimes:jpg,jpeg,png,webp';
                    break;
                case 'sample_point_select':
                case 'analysis_elements_select':
                    // Multiple select fields should be arrays when submitted
                    if ($this->isMultipleSelectField($element, $request)) {
                        $elementRules[] = 'array';
                    }
                    break;

                case 'user_select':
                    $elementRules[] = 'integer';
                    $elementRules[] = 'exists:users,id';
                    break;
                case 'zone_select':
                    $elementRules[] = 'string';
                    $elementRules[] = 'exists:zones,id';
                    break;
            }

            // Custom validation rules
            if ($element->validation_rules) {
                $elementRules = array_merge($elementRules, $element->validation_rules);
            }

            // Handle array fields (from rows sections or multiple selects)
            if ($request->has($fieldName) && is_array($request->input($fieldName))) {
                // Check if this is a multiple select field
                if ($this->isMultipleSelectField($element, $request)) {
                    // For multiple select fields, validate the array itself
                    // dd($element,$request->all());
                    $rules[$fieldName] = $elementRules;
                    // Also validate each individual value
                    $rules[$fieldName . '.*'] = ['array']; // Each selected value should be a string
                } else {
                    // For rows sections, validate each array element
                    $rules[$fieldName . '.*'] = $elementRules;
                }
            } else {
                $rules[$fieldName] = $elementRules;
            }
        }

        return $rules;
    }

    /**
     * Process form data and save values
     */
    private function processFormData(SubmissionFormInstance $instance, Request $request, $elements): void
    {
        foreach ($elements as $element) {
            $fieldName = $element->name;
            // Check if this is a multiple select field (has [] in form name but not from rows)
            $isMultipleSelect = $this->isMultipleSelectField($element, $request);
            
            $inputValue = $request->input($fieldName);
            $fileValue = $request->file($fieldName);
            $isArray = is_array($inputValue) || is_array($fileValue);

            if ($isArray) {
                if ($isMultipleSelect) {
                    $this->processMultipleSelectField($instance, $element, $inputValue ?? []);
                } else {
                    // Handle array fields (from rows sections)
                    $this->processArrayField($instance, $element, $inputValue ?? [], $request, $fieldName);
                }
            } else {
                $this->processSingleField($instance, $element, $inputValue, $request);
            }
        }
    }

    /**
     * Process a single field value
     */
    private function processSingleField(SubmissionFormInstance $instance, SubmissionFormElement $element, $value, Request $request): void
    {
        if (($value === null || $value === '') && $element->element_type === 'user_select' && Auth::check()) {
            $value = Auth::id();
        }

        // Handle file uploads
        if (in_array($element->element_type, ['file', 'camera_photo', 'image_upload'], true) && $request->hasFile($element->name)) {
            $file = $request->file($element->name);
            $filename = time() . '_' . Str::slug($element->name) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('submission-forms/' . $instance->id, $filename, 'public');

            $this->saveFieldValue($instance, $element, null, $path);
        } else {
            $this->saveFieldValue($instance, $element, $value);
        }
    }

    /**
     * Process array field values (from rows sections)
     */
    private function processArrayField(SubmissionFormInstance $instance, SubmissionFormElement $element, array $values, Request $request = null, string $fieldName = null): void
    {
        $inputs = $values;
        $files = [];

        if ($request && $fieldName) {
            $files = $request->file($fieldName) ?? [];
        }

        $indices = array_unique(array_merge(array_keys($inputs), array_keys($files)));
        sort($indices);

        // Fetch existing values for this element to preserve already-uploaded files
        $existingValues = $instance->values()
            ->where('submission_form_element_id', $element->id)
            ->get()
            ->keyBy('array_index');

        SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element): void {
            $instance->values()->where('submission_form_element_id', $element->id)->delete();
        });

        // Save each value with array index
        foreach ($indices as $index) {
            $inputValue = $inputs[$index] ?? null;
            $fileValue = $files[$index] ?? null;

            $filePath = null;
            $saveValue = $inputValue;

            if (in_array($element->element_type, ['file', 'camera_photo', 'image_upload'], true)) {
                if ($fileValue instanceof \Illuminate\Http\UploadedFile) {
                    // Save new file upload
                    $filename = time() . '_' . $index . '_' . Str::slug($element->name) . '.' . $fileValue->getClientOriginalExtension();
                    $filePath = $fileValue->storeAs('submission-forms/' . $instance->id, $filename, 'public');
                    $saveValue = null;
                } else {
                    // Keep existing file if it was already uploaded and no new file was uploaded
                    $existing = $existingValues->get($index);
                    if ($existing && $existing->file_path) {
                        $filePath = $existing->file_path;
                        $saveValue = null;
                    }
                }
            }

            if (($saveValue === null || $saveValue === '') && $element->element_type === 'user_select' && Auth::check()) {
                $saveValue = Auth::id();
            }

            if (($saveValue !== null && $saveValue !== '') || $filePath !== null) {
                $this->saveFieldValue($instance, $element, $saveValue, $filePath, $index);
            }
        }
    }

    /**
     * Check if this is a multiple select field
     */
    private function isMultipleSelectField(SubmissionFormElement $element, Request $request): bool
    {
        // Check for specific element types that support multiple selection
        $multipleSelectTypes = ['sample_point_select', 'analysis_type_select', 'analysis_elements_select'];

        if (in_array($element->element_type, $multipleSelectTypes)) {
            return true;
        }

        // Check if element has multiple property set
        if (isset($element->properties['multiple']) && $element->properties['multiple'] === true) {
            return true;
        }

        return false;
    }

    /**
     * Process multiple select field values
     */
    private function processMultipleSelectField(SubmissionFormInstance $instance, SubmissionFormElement $element, array $values): void
    {
        // Filter out empty values and convert to comma-separated string
        $filteredValues = array_filter($values, function ($value) {
            return $value !== null && $value !== '';
        });

        $convertTOArray = [];

        foreach ($filteredValues as $value) {
            $convertTOArray[] = implode(',', $value);
        }

        $this->processArrayField($instance, $element, $convertTOArray);
    }

    /**
     * Save a field value to the database
     */
    private function saveFieldValue(SubmissionFormInstance $instance, SubmissionFormElement $element, $value, $filePath = null, $arrayIndex = null): void
    {
        SubmissionFormInstanceValue::withoutAuditing(function () use ($instance, $element, $value, $filePath, $arrayIndex): void {
            SubmissionFormInstanceValue::updateOrCreate(
                [
                    'submission_form_instance_id' => $instance->id,
                    'submission_form_element_id' => $element->id,
                    'array_index' => $arrayIndex,
                ],
                [
                    'value' => $value,
                    'file_path' => $filePath,
                ]
            );
        });

        // // Process field mapping if configured
        // if ($element->isMapped()) {
        //     $this->processFieldMapping($element, $value, $arrayIndex);
        // }
    }

    /**
     * Process field mapping to database tables
     */
    private function processFieldMapping(SubmissionFormElement $element, $value, $arrayIndex = null): void
    {
        $mapping = $element->getMappingConfig();

        if (!$mapping || !$value) {
            return;
        }

        $data = [
            $mapping['field'] => $value,
            'updated_at' => now()
        ];

        if ($mapping['table'] === 'sample_headers') {
            // Handle sample headers mapping
            if ($arrayIndex !== null) {
                // For array fields, create separate records
                SampleHeader::create($data);
            } else {
                // For single fields, update or create
                SampleHeader::updateOrCreate(
                    ['submission_form_element_id' => $element->id],
                    $data
                );
            }
        } elseif ($mapping['table'] === 'sample_details') {
            // Handle sample details mapping
            if ($arrayIndex !== null) {
                // For array fields, create separate records
                SampleDetails::create($data);
            } else {
                // For single fields, update or create
                SampleDetails::updateOrCreate(
                    ['submission_form_element_id' => $element->id],
                    $data
                );
            }
        }
    }

    /**
     * Get dynamic options for custom elements
     */
    public function getDynamicOptions(Request $request)
    {
        $elementType = $request->get('element_type');

        // Allow public access for certain element types (for public form submissions)
        $publicElementTypes = ['sample_condition_select', 'standard_select', 'sample_type_select', 'analysis_elements_select'];

        if (!Auth::check() && !in_array($elementType, $publicElementTypes)) {
            Log::warning('Unauthenticated request to dynamic options for restricted element type', ['element_type' => $elementType]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
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
            'sample_type_id' => $sampleTypeId,
            'store_id' => $storeId,
            'search' => $search,
            'page' => $page
        ]);

        try {
            $options = [];

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
                            'id' => $client->id,
                            'text' => $client->name,
                            'value' => $client->id,
                            'label' => $client->name
                        ];
                    }

                    // Return with pagination metadata
                    return response()->json([
                        'success' => true,
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
                    $query = \App\SampleType::select('id', 'name as text', 'name as label')->orderBy('name');

                    // If submission form ID is present, filter by associated lab sections
                    if ($request->has('submission_form_id')) {
                        $formId = $request->get('submission_form_id');
                        $form = \App\Models\SubmissionForm::with('sampleAnalysisStages')->find($formId);

                        if ($form && $form->sampleAnalysisStages->isNotEmpty()) {
                            $stageIds = $form->sampleAnalysisStages->pluck('id')->toArray();

                            $query->whereHas('sampleAnalysisStages', function ($q) use ($stageIds) {
                                $q->whereIn('sample_analysis_stages.id', $stageIds);
                            });
                        }
                    }

                    $options = $query->get()->toArray();

                    // Add value key for compatibility
                    foreach ($options as &$option) {
                        $option['value'] = $option['id'];
                    }
                    break;

                case 'client_unit_select':
                    if ($clientId) {
                        $options = \App\Models\CRM\CRMCompanyUnit::where('crm_customer_id', $clientId)
                            ->select('id', 'name as text')
                            ->get()
                            ->toArray();
                    }
                    break;

                case 'client_contact_select':
                    if ($clientId) {
                        $options = \App\Models\CRM\CustomerContact::where('crm_customer_id', $clientId)
                            ->select('id', 'name as text')
                            ->get()
                            ->toArray();
                    }
                    break;

                case 'client_submission_officers_select':
                    if ($clientId) {
                        $officers = \App\Models\CRM\CustomerContact::where('crm_customer_id', $clientId)
                            ->where('active', 1)
                            ->where('can_submit_sample', 1)
                            ->get();

                        $options = [];
                        foreach ($officers as $officer) {
                            $fullName = trim($officer->first_name.' '.$officer->middle_name.' '.$officer->last_name);
                            $options[] = [
                                'id' => $officer->id,
                                'text' => $fullName !== '' ? $fullName : 'Contact',
                            ];
                        }
                    }
                    break;

                case 'store_select':
                    $options = \App\InventoryStore::select('id', 'name as text')
                        ->get()
                        ->toArray();
                    break;

                case 'store_slot_select':
                    if ($storeId) {
                        $options = \App\InventoryStoreSlot::where('store_id', $storeId)
                            ->select('id', 'name as text')
                            ->get()
                            ->toArray();
                    }
                    break;

                case 'sample_condition_select':
                    $options = \App\SampleCondition::select('id', 'name as text')
                        ->get()
                        ->toArray();
                    break;

                case 'analysis_type_select':
                    if ($sampleTypeId) {
                        $options = \App\AnalysisType::where('sample_type_id', $sampleTypeId)
                            ->select('id', 'name as text')
                            ->get()
                            ->toArray();
                    }
                    break;

                case 'analysis_elements_select':
                    $analysisTypeId = $request->get('analysis_type_id');

                    if ($analysisTypeId) {
                        $elements = \App\AnalysisElements::where('analysis_type_id', $analysisTypeId)
                            ->where('active', 1)
                            ->with('analyte')
                            ->get();

                        $options = $elements->map(function ($element) {
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
                                $methodName = $method ? ($method->name ?? $element->method) : $element->method;
                            }

                            return [
                                'id' => $element->id,
                                'text' => $parameterName . ' (' . $methodName . ')'
                            ];
                        })->toArray();
                    } else {
                        $options = [];
                    }
                    break;

                case 'standard_select':
                    $options = \App\Standards::select('id', 'name as text')
                        ->get()
                        ->toArray();
                    break;

                case 'sample_point_select':
                    if ($clientUnitId) {
                        $samplePoints = \App\Models\CRM\SamplePoint::where('crm_company_unit_id', $clientUnitId)
                            ->with('area')
                            ->get();

                        $options = [];
                        foreach ($samplePoints as $samplePoint) {
                            // Format: "area - sample point" if area exists, otherwise just "sample point"
                            $text = $samplePoint->area && $samplePoint->area->name
                                ? $samplePoint->area->name . ' - ' . $samplePoint->name
                                : $samplePoint->name;

                            $options[] = [
                                'id' => $samplePoint->id,
                                'text' => $text
                            ];
                        }
                    }
                    break;

                case 'user_select':
                    $query = \App\User::query()
                        ->where('active', 1)
                        ->where('is_client', 0)
                        ->whereNull('supplier_id');

                    if (Auth::check() && function_exists('getUserCompany')) {
                        $query->where('company_id', getUserCompany());
                    }

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
                            'id' => $user->id,
                            'text' => $label,
                            'value' => $user->id,
                            'label' => $label
                        ];
                    }

                    if (empty($options)) {
                        $options[] = [
                            'id' => '',
                            'text' => 'No users available',
                            'value' => '',
                            'label' => 'No users available'
                        ];
                    }

                    return response()->json([
                        'success' => true,
                        'options' => $options,
                        'pagination' => [
                            'current_page' => $paginator->currentPage(),
                            'per_page' => $paginator->perPage(),
                            'total' => $paginator->total(),
                            'has_more' => $paginator->hasMorePages()
                        ]
                    ]);

                default:
                    Log::warning('Unknown element type: ' . $elementType);
                    return response()->json(['success' => false, 'message' => 'Unknown element type']);
            }

            Log::info('Returning options', ['count' => count($options)]);

            return response()->json([
                'success' => true,
                'options' => $options
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting dynamic options: ' . $e->getMessage());
            Log::error('Error file: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Error stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error loading options: ' . $e->getMessage()]);
        }
    }

    /**
     * Resolve value for depended_field elements based on selected source record.
     */
    public function getDependedFieldValue(Request $request)
    {
        $validated = $request->validate([
            'element_id' => 'required|uuid|exists:submission_form_elements,id',
            'source_id' => 'required',
        ]);

        $element = SubmissionFormElement::find($validated['element_id']);

        if (! $element || $element->element_type !== 'depended_field') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid depended field element.',
            ], 422);
        }

        $sourceTable = (string) ($element->source_table ?? '');
        $sourceField = (string) ($element->source_field ?? '');

        if ($sourceTable === '' || $sourceField === '') {
            return response()->json([
                'success' => false,
                'message' => 'Depended field source is not configured.',
            ], 422);
        }

        if (! Schema::hasTable($sourceTable) || ! Schema::hasColumn($sourceTable, 'id') || ! Schema::hasColumn($sourceTable, $sourceField)) {
            return response()->json([
                'success' => false,
                'message' => 'Depended field source is unavailable.',
            ], 422);
        }

        $sourceId = $validated['source_id'];
        if (is_string($sourceId) && str_contains($sourceId, ',')) {
            $sourceId = trim(explode(',', $sourceId)[0]);
        }

        $record = DB::table($sourceTable)
            ->where('id', $sourceId)
            ->first([$sourceField]);

        return response()->json([
            'success' => true,
            'value' => $record ? data_get($record, $sourceField) : null,
        ]);
    }

    /**
     * Display submission form instance with all linked batches and samples
     */
    public function batchView(SubmissionFormInstance $instance)
    {
        // Load form instance with all relationships
        $instance->load([
            'submissionForm.sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'values.element',
            'batches.samples',
            'batches.sample_type'
        ]);

        // Get company information
        $company = getActiveCompany();

        // Get all batches linked to this form instance
        $batches = $instance->batches()->with([
            'sample_type',
            'samples' => function ($query) {
                $query->orderBy('sample_code', 'asc');
            }
        ])->get();

        // Group samples by sample_type_id and then by analysis_type_id
        $groupedSamples = [];

        foreach ($batches as $batch) {
            $sampleTypeId = $batch->sample_type_id;
            $sampleTypeName = $batch->sample_type ? $batch->sample_type->name : 'Unknown Type';

            if (!isset($groupedSamples[$sampleTypeId])) {
                $groupedSamples[$sampleTypeId] = [
                    'name' => $sampleTypeName,
                    'analyses' => []
                ];
            }

            foreach ($batch->samples as $sample) {
                // Get analysis types for this sample
                $analysisRelations = \App\SampleAnalysisTypeRelation::where('sample_detail_id', $sample->id)->get();

                foreach ($analysisRelations as $relation) {
                    $analysisTypeId = $relation->analysis_type_id;

                    // Get analysis type details
                    $analysisType = \App\AnalysisType::find($analysisTypeId);

                    if ($analysisType) {
                        $analysisTypeName = $analysisType->name;

                        if (!isset($groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId])) {
                            $groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId] = [
                                'name' => $analysisTypeName,
                                'samples' => []
                            ];
                        }

                        $groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId]['samples'][] = $sample;
                    }
                }
            }
        }

        // Process grouped samples to calculate counts and ranges
        $processedSampleData = [];
        foreach ($groupedSamples as $sampleTypeId => $sampleTypeData) {
            $processedAnalyses = [];

            foreach ($sampleTypeData['analyses'] as $analysisTypeId => $analysisData) {
                $samples = collect($analysisData['samples'])->unique('id');
                $sampleCodes = $samples->pluck('sample_code')->sort()->values();

                // Get min and max sample codes
                $minCode = $sampleCodes->first();
                $maxCode = $sampleCodes->last();
                $codeRange = $minCode === $maxCode ? $minCode : "{$minCode} - {$maxCode}";

                // Collect reporting info
                $sampleIds = $samples->pluck('id')->toArray();
                $results = \App\CapturedResult::whereIn('sample_detail_id', $sampleIds)
                    ->where('analysis_type_id', $analysisTypeId)
                    ->whereNotNull('result')
                    ->with('operator')
                    ->get();

                $reporters = $results->pluck('operator.name')->unique()->filter()->implode(', ');
                $reportDate = $results->max('updated_at');
                $reportedInfo = $reporters ? $reporters . ($reportDate ? " (" . $reportDate->format('d/m/Y') . ")" : "") : 'Pending';

                $processedAnalyses[] = [
                    'analysis_type_name' => $analysisData['name'],
                    'sample_count' => $samples->count(),
                    'code_range' => $codeRange,
                    'reported_info' => $reportedInfo,
                    'sent_info' => 'Pending',
                ];
            }

            // If no analyses were found through relations, but we have samples, add a generic row
            if (empty($processedAnalyses)) {
                $batchForThisType = $batches->where('sample_type_id', $sampleTypeId)->first();
                if ($batchForThisType) {
                    $allSamples = $batchForThisType->samples ? $batchForThisType->samples->unique('id') : collect();
                    $sampleCount = $allSamples->count();

                    if ($sampleCount > 0) {
                        $sampleCodes = $allSamples->pluck('sample_code')->sort()->values();
                        $minCode = $sampleCodes->first();
                        $maxCode = $sampleCodes->last();
                        $codeRange = $minCode === $maxCode ? $minCode : "{$minCode} - {$maxCode}";
                    } else {
                        // Check for staging data first
                        $staging = \App\Models\SampleDetailStaging::where('sample_header_id', $batchForThisType->id)->first();
                        if ($staging && isset($staging->data_json['quantity'])) {
                            $sampleCount = $staging->data_json['quantity'];
                        } else {
                            // Fallback to form field values for quantity and range
                            $sampleCount = 0;
                            $possibleKeywords = ["no_of_samples", "no_samples", "number_of_samples", "quantity", "total_samples", "count"];
                            foreach ($possibleKeywords as $keyword) {
                                $val = $instance->getValueByElementName($keyword);
                                if ($val !== null && is_numeric($val) && $val > 0) {
                                    $sampleCount = $val;
                                    break;
                                }
                            }
                        }

                        if ($sampleCount == 0) {
                            $instance->values()->with("element")->get()->each(function ($v) use (&$sampleCount) {
                                if ($sampleCount > 0)
                                    return;
                                $label = strtolower($v->element->label ?? "");
                                if (
                                    (str_contains($label, "number") && str_contains($label, "sample")) ||
                                    (str_contains($label, "no") && str_contains($label, "sample")) ||
                                    str_contains($label, "quantity")
                                ) {
                                    if (is_numeric($v->value) && $v->value > 0) {
                                        $sampleCount = $v->value;
                                    }
                                }
                            });
                        }
                        $codeRange = 'Pending Assignment';
                    }

                    // Try to get test names from form values
                    $testsRequired = $instance->resolveDisplayValueByName('tests_required')
                        ?: $instance->resolveDisplayValueByName('analysis_elements_select')
                        ?: $instance->resolveDisplayValueByName('analysis_type_id')
                        ?: 'General Analysis';

                    $processedAnalyses[] = [
                        'analysis_type_name' => strip_tags($testsRequired),
                        'sample_count' => $sampleCount,
                        'code_range' => $codeRange,
                        'reported_info' => 'Pending',
                        'sent_info' => 'Pending',
                    ];
                }
            }

            $processedSampleData[] = [
                'sample_type_name' => $sampleTypeData['name'],
                'analyses' => $processedAnalyses
            ];
        }

        $testsRequiredTableGroups = app(\App\Services\SubmissionFormPdfService::class)->buildTestsRequiredTableGroups($instance);

        $displaySections = app(\App\Services\SubmissionForm\SubmissionFormSchemaHelper::class)
            ->uniqueSections($instance->submissionForm);

        return view('submission-forms.instances.batch-view', compact(
            'instance',
            'company',
            'batches',
            'processedSampleData',
            'testsRequiredTableGroups',
            'displaySections'
        ));
    }

    public function batchViewPrint(SubmissionFormInstance $instance)
    {
        // Load form instance with all relationships
        $instance->load([
            'submissionForm.sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            },
            'values.element',
            'batches.samples',
            'batches.sample_type'
        ]);

        // Get company information
        $company = getActiveCompany();

        // Check if the form has a custom print template
        $submissionForm = $instance->submissionForm;
        $templateName = $submissionForm->getPrintTemplateName();

        // If a custom template exists, use it with the appropriate data format
        if ($templateName !== 'submission-forms.print.default' && view()->exists($templateName)) {
            // Prepare data in the format expected by custom templates (e.g., microbiology, serology)
            $existingValues = $instance->getSubmittedFormData();
            $pdfService = app(\App\Services\SubmissionFormPdfService::class);
            $existingValues['sample_header'] = $pdfService->prepareSampleHeaderForPdf($existingValues['sample_header'] ?? [], $instance);
            $processedSampleData = $pdfService->buildProcessedSampleData($instance);
            $testsRequiredTableGroups = $pdfService->buildTestsRequiredTableGroups($instance);
            $logoUrl = '';
            if ($company && ! empty($company->logo)) {
                $logoUrl = str_starts_with($company->logo, 'http') ? $company->logo : url($company->logo);
            }
            return view($templateName, compact('submissionForm', 'instance', 'existingValues', 'processedSampleData', 'testsRequiredTableGroups', 'logoUrl'));
        }

        // Otherwise, use the default batch view print template
        // Get all batches linked to this form instance
        $batches = $instance->batches()->with([
            'sample_type',
            'samples' => function ($query) {
                $query->orderBy('sample_code', 'asc');
            }
        ])->get();

        // Group samples by sample_type_id and then by analysis_type_id
        $groupedSamples = [];

        foreach ($batches as $batch) {
            $sampleTypeId = $batch->sample_type_id;
            $sampleTypeName = $batch->sample_type ? $batch->sample_type->name : 'Unknown Type';

            if (!isset($groupedSamples[$sampleTypeId])) {
                $groupedSamples[$sampleTypeId] = [
                    'name' => $sampleTypeName,
                    'analyses' => []
                ];
            }

            foreach ($batch->samples as $sample) {
                // Get analysis types for this sample
                $analysisRelations = \App\SampleAnalysisTypeRelation::where('sample_detail_id', $sample->id)->get();

                foreach ($analysisRelations as $relation) {
                    $analysisTypeId = $relation->analysis_type_id;

                    // Get analysis type details
                    $analysisType = \App\AnalysisType::find($analysisTypeId);

                    if ($analysisType) {
                        $analysisTypeName = $analysisType->name;

                        if (!isset($groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId])) {
                            $groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId] = [
                                'name' => $analysisTypeName,
                                'samples' => []
                            ];
                        }

                        $groupedSamples[$sampleTypeId]['analyses'][$analysisTypeId]['samples'][] = $sample;
                    }
                }
            }
        }

        // Process grouped samples to calculate counts and ranges
        $processedSampleData = [];
        foreach ($groupedSamples as $sampleTypeId => $sampleTypeData) {
            $processedAnalyses = [];

            foreach ($sampleTypeData['analyses'] as $analysisTypeId => $analysisData) {
                $samples = collect($analysisData['samples'])->unique('id');
                $sampleCodes = $samples->pluck('sample_code')->sort()->values();

                // Get min and max sample codes
                $minCode = $sampleCodes->first();
                $maxCode = $sampleCodes->last();
                $codeRange = $minCode === $maxCode ? $minCode : "{$minCode} - {$maxCode}";

                // Collect reporting info
                $sampleIds = $samples->pluck('id')->toArray();
                $results = \App\CapturedResult::whereIn('sample_detail_id', $sampleIds)
                    ->where('analysis_type_id', $analysisTypeId)
                    ->whereNotNull('result')
                    ->with('operator')
                    ->get();

                $reporters = $results->pluck('operator.name')->unique()->filter()->implode(', ');
                $reportDate = $results->max('updated_at');
                $reportedInfo = $reporters ? $reporters . ($reportDate ? " (" . $reportDate->format('d/m/Y') . ")" : "") : 'Pending';

                $processedAnalyses[] = [
                    'analysis_type_name' => $analysisData['name'],
                    'sample_count' => $samples->count(),
                    'code_range' => $codeRange,
                    'reported_info' => $reportedInfo,
                    'sent_info' => 'Pending',
                ];
            }

            // If no analyses were found through relations, but we have samples, add a generic row
            if (empty($processedAnalyses)) {
                $batchForThisType = $batches->where('sample_type_id', $sampleTypeId)->first();
                if ($batchForThisType) {
                    $allSamples = $batchForThisType->samples ? $batchForThisType->samples->unique('id') : collect();
                    $sampleCount = $allSamples->count();

                    if ($sampleCount > 0) {
                        $sampleCodes = $allSamples->pluck('sample_code')->sort()->values();
                        $minCode = $sampleCodes->first();
                        $maxCode = $sampleCodes->last();
                        $codeRange = $minCode === $maxCode ? $minCode : "{$minCode} - {$maxCode}";
                    } else {
                        // Check for staging data first
                        $staging = \App\Models\SampleDetailStaging::where('sample_header_id', $batchForThisType->id)->first();
                        if ($staging && isset($staging->data_json['quantity'])) {
                            $sampleCount = $staging->data_json['quantity'];
                        } else {
                            // Fallback to form field values for quantity and range
                            $sampleCount = $instance->getValueByElementName('no_of_samples') ?: $instance->getValueByElementName('quantity') ?: 0;
                        }
                        $codeRange = 'Pending Assignment';
                    }

                    // Try to get test names from form values
                    $testsRequired = $instance->resolveDisplayValueByName('tests_required')
                        ?: $instance->resolveDisplayValueByName('analysis_elements_select')
                        ?: $instance->resolveDisplayValueByName('analysis_type_id')
                        ?: 'General Analysis';

                    $processedAnalyses[] = [
                        'analysis_type_name' => strip_tags($testsRequired),
                        'sample_count' => $sampleCount,
                        'code_range' => $codeRange,
                        'reported_info' => 'Pending',
                        'sent_info' => 'Pending',
                    ];
                }
            }

            $processedSampleData[] = [
                'sample_type_name' => $sampleTypeData['name'],
                'analyses' => $processedAnalyses
            ];
        }

        $testsRequiredTableGroups = app(\App\Services\SubmissionFormPdfService::class)->buildTestsRequiredTableGroups($instance);

        $displaySections = app(\App\Services\SubmissionForm\SubmissionFormSchemaHelper::class)
            ->uniqueSections($instance->submissionForm);

        return view('submission-forms.instances.batch-view-print', compact(
            'instance',
            'company',
            'batches',
            'processedSampleData',
            'testsRequiredTableGroups',
            'displaySections'
        ));
    }

    public function downloadTrfPdf(SubmissionForm $submissionForm, SubmissionFormInstance $instance, TestRequestFormPdfService $service)
    {
        $this->abortIfInstanceFormMismatch($submissionForm, $instance);
        $this->authorizeViewInstance($submissionForm, $instance, 'You are not authorized to download this TRF PDF.');

        $code = strtoupper((string) ($submissionForm->document_code ?? ''));
        if (! str_starts_with($code, 'TRF-')) {
            abort(404, 'This form is not a test request form.');
        }

        return $service->stream($instance);
    }

    private function abortIfInstanceFormMismatch(SubmissionForm $submissionForm, SubmissionFormInstance $instance): void
    {
        if ((string) $instance->submission_form_id !== (string) $submissionForm->id) {
            abort(404);
        }
    }

    private function authorizeViewInstance(SubmissionForm $submissionForm, SubmissionFormInstance $instance, string $message): void
    {
        if (! Gate::forUser(Auth::user())->allows('view', $instance)) {
            abort(403, $message);
        }
    }

    private function authorizeManageInstance(SubmissionForm $submissionForm, SubmissionFormInstance $instance, string $message): void
    {
        if (! Gate::forUser(Auth::user())->allows('update', $instance)) {
            abort(403, $message);
        }
    }

    /**
     * Generate Sample Collection / Registration labels (one type per request).
     */
    public function sampleCollectionLabel(Request $request, SubmissionFormInstance $instance)
    {
        $instance->load([
            'submissionForm',
            'crmCustomer',
            'values.element',
            'sampleSubmissionRequest.batch.samples',
            'batches.samples',
        ]);

        $labelType = strtolower(trim((string) $request->query('type', 'collection')));
        if (! in_array($labelType, ['collection', 'registration'], true)) {
            $labelType = 'collection';
        }

        // Prefer active company logo (main app pattern), then fallback to legacy system config key.
        $activeCompany = \App\Company::query()
            ->where('active', 1)
            ->orderByDesc('updated_at')
            ->first();

        $logoPath = null;
        if ($activeCompany) {
            $logoPath = $activeCompany->report_logo ?: $activeCompany->logo;
        }

        if (empty($logoPath)) {
            $companyLogoConfig = \App\Models\System\SystemConfiguration::query()
                ->where('key', 'company_logo')
                ->first();
            $logoPath = $companyLogoConfig ? $companyLogoConfig->value : null;
        }

        // Embed as data URI so the logo renders on-screen and when printing (no external fetch).
        $logoSrc = $this->resolveLabelLogoDataUri(is_string($logoPath) ? $logoPath : null);

        $formData = [];
        foreach ($instance->values as $value) {
            $fieldName = $value->element?->name ?? $value->element_name ?? null;
            if ($fieldName) {
                $formData[$fieldName] = $value->value;
            }
        }

        $sampleLines = app(\App\Services\SubmissionForm\SubmissionRequestSampleLineService::class)
            ->linesForInstance($instance);

        // Get job number.
        // For subcontracted requests awaiting dispatch, job number must remain empty
        // and only be shown once a real batch/job has been created after dispatch.
        $submissionRequest = $instance->sampleSubmissionRequest;
        if (! $submissionRequest) {
            $submissionRequest = SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', (string) $instance->id)
                ->first();
        }

        $isAwaitingSubcontractDispatch = $submissionRequest !== null
            && $submissionRequest->needsSubcontractDispatch();

        if ($isAwaitingSubcontractDispatch) {
            $jobNumber = '';
        } else {
            $jobNumber = $this->plainLabelText((string) ($formData['job_number'] ?? ''));

            if ($this->isBlankLabelValue($jobNumber) && $submissionRequest?->sample_header_id) {
                $jobNumber = $this->plainLabelText((string) (optional($submissionRequest->batch)->batch_code ?? ''));
            }

            if ($this->isBlankLabelValue($jobNumber)) {
                $jobNumber = $this->plainLabelText((string) (
                    $instance->batches->pluck('batch_code')->filter()->first() ?? ''
                ));
            }

            // Job ID is the lab job/batch number only — never the TRF/form number.
            if ($this->isBlankLabelValue($jobNumber) || $this->isUuidLike($jobNumber)) {
                $jobNumber = '';
            }
        }

        $customerName = $this->firstResolvedLabelValue(
            $instance,
            ['customer_name', 'client_name'],
            (string) ($instance->crmCustomer->name ?? $formData['customer_name'] ?? $formData['client_name'] ?? '')
        );
        $customerAddress = $this->firstResolvedLabelValue(
            $instance,
            ['customer_address', 'address'],
            (string) ($instance->crmCustomer->physical_address ?? $formData['customer_address'] ?? $formData['address'] ?? '')
        );
        $customerPhone = $this->firstResolvedLabelValue(
            $instance,
            ['customer_phone', 'phone', 'mobile_number'],
            (string) ($instance->crmCustomer->telephone1 ?? $formData['customer_phone'] ?? $formData['phone'] ?? '')
        );

        $sampleType = $this->firstResolvedLabelValue(
            $instance,
            ['type_of_samples', 'sample_type', 'sample_types_ww', 'sample_type_select'],
            (string) ($formData['type_of_samples'] ?? $formData['sample_type'] ?? '')
        );

        $sampleName = $this->firstResolvedLabelValue(
            $instance,
            ['sample_name', 'sample_description', 'sample_information'],
            (string) ($formData['sample_name'] ?? $formData['sample_description'] ?? $formData['sample_information'] ?? '')
        );

        $sampleDescription = $this->firstResolvedLabelValue(
            $instance,
            ['sample_description', 'sample_name', 'sample_information'],
            (string) ($formData['sample_description'] ?? $formData['sample_name'] ?? $formData['sample_information'] ?? '')
        );

        $siteLocation = $this->resolveSiteLocationForLabel($instance, $sampleLines, $formData);
        $samplingPoint = $siteLocation;

        $samplingDate = $this->plainLabelText(
            (string) ($formData['date_of_sampling'] ?? $formData['sampling_date'] ?? $formData['collection_date'] ?? now()->format('Y-m-d'))
        );
        $dateTimeOfCollection = $this->resolveCollectionDateTime($formData);

        $batchNumberFromForm = $this->firstResolvedLabelValue(
            $instance,
            ['batch_number'],
            (string) ($formData['batch_number'] ?? '')
        );
        $batchNumber = ! $this->isBlankLabelValue($batchNumberFromForm)
            ? $batchNumberFromForm
            : ($this->isBlankLabelValue((string) $jobNumber) ? 'N/A' : (string) $jobNumber);

        $clientName = $customerName;
        $sampleTemperature = $this->firstResolvedLabelValue(
            $instance,
            ['sample_temp', 'field_sample_temp'],
            (string) ($formData['sample_temp'] ?? $formData['field_sample_temp'] ?? '')
        );
        $collectedBy = $this->firstResolvedLabelValue(
            $instance,
            ['collected_by', 'sampled_by'],
            (string) ($formData['collected_by'] ?? $formData['sampled_by'] ?? '')
        );
        $preservationApplied = $this->firstResolvedLabelValue(
            $instance,
            ['preservation_applied'],
            (string) ($formData['preservation_applied'] ?? 'No')
        );
        $containerType = $this->firstResolvedLabelValue(
            $instance,
            ['container_type'],
            (string) ($formData['container_type'] ?? '')
        );
        $sampleCollectionFor = $this->firstResolvedLabelValue(
            $instance,
            ['sample_collection_for'],
            (string) ($formData['sample_collection_for'] ?? '')
        );

        $sampleId = $this->resolveSampleIdForLabel($instance, $sampleLines, $formData);
        $testRequirement = $this->resolveTestRequirementForLabel($instance, $sampleLines, $formData);

        $sampleRows = $formData['sample_rows'] ?? [];

        return view('submission-forms.instances.sample-collection-label', compact(
            'instance',
            'logoPath',
            'logoSrc',
            'labelType',
            'jobNumber',
            'customerName',
            'customerAddress',
            'customerPhone',
            'sampleType',
            'sampleDescription',
            'samplingDate',
            'samplingPoint',
            'sampleRows',
            'formData',
            'sampleName',
            'batchNumber',
            'clientName',
            'siteLocation',
            'dateTimeOfCollection',
            'sampleTemperature',
            'collectedBy',
            'preservationApplied',
            'containerType',
            'sampleCollectionFor',
            'sampleId',
            'testRequirement'
        ));
    }

    private function resolveLabelLogoDataUri(?string $logoPath): string
    {
        $absolutePath = $this->resolveLabelLogoAbsolutePath($logoPath);
        if ($absolutePath === '') {
            return '';
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            return '';
        }

        $ext = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            default => 'image/png',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    private function resolveLabelLogoAbsolutePath(?string $logoPath): string
    {
        $candidates = [];

        $normalized = trim((string) $logoPath);
        if ($normalized !== '') {
            if (Str::startsWith($normalized, ['http://', 'https://'])) {
                $normalized = (string) (parse_url($normalized, PHP_URL_PATH) ?? $normalized);
            }

            $normalized = ltrim($normalized, '/');
            $filename = basename($normalized);

            if ($filename !== '') {
                $relative = preg_replace('#^storage/#', '', $normalized);
                if (is_string($relative) && $relative !== $normalized) {
                    $candidates[] = \Illuminate\Support\Facades\Storage::disk('public')->path($relative);
                }

                $candidates[] = storage_path('app/companies/'.$filename);
                $candidates[] = public_path($normalized);
                $candidates[] = public_path('storage/companies/'.$filename);
            }
        }

        $candidates[] = public_path('images/company_logo.png');
        $candidates[] = public_path('images/logo.png');

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_readable($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    /**
     * @param  list<string>  $elementNames
     */
    private function firstResolvedLabelValue(SubmissionFormInstance $instance, array $elementNames, string $fallback = ''): string
    {
        foreach ($elementNames as $elementName) {
            $display = $instance->resolveDisplayValueByName($elementName);
            if ($display === null) {
                continue;
            }

            $plain = $this->plainLabelText(is_scalar($display) ? (string) $display : '');
            if (! $this->isBlankLabelValue($plain) && ! $this->isUuidLike($plain)) {
                return $plain;
            }
        }

        $fallbackPlain = $this->plainLabelText($fallback);

        return (! $this->isBlankLabelValue($fallbackPlain) && ! $this->isUuidLike($fallbackPlain))
            ? $fallbackPlain
            : 'N/A';
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @param  array<string, mixed>  $formData
     */
    private function resolveSiteLocationForLabel(
        SubmissionFormInstance $instance,
        array $sampleLines,
        array $formData
    ): string {
        $fromLines = collect($sampleLines)
            ->map(fn (array $line): string => $this->plainLabelText((string) (($line['sampling_point'] ?? null) ?: ($line['location'] ?? ''))))
            ->reject(fn (string $value): bool => $this->isBlankLabelValue($value) || $this->isUuidLike($value))
            ->unique()
            ->values();

        if ($fromLines->isNotEmpty()) {
            return $fromLines->implode(', ');
        }

        return $this->firstResolvedLabelValue(
            $instance,
            ['sampling_location', 'sampling_point', 'location'],
            (string) ($formData['sampling_location'] ?? $formData['sampling_point'] ?? $formData['location'] ?? '')
        );
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @param  array<string, mixed>  $formData
     */
    private function resolveSampleIdForLabel(
        SubmissionFormInstance $instance,
        array $sampleLines,
        array $formData
    ): string {
        $fromLines = collect($sampleLines)
            ->map(fn (array $line): string => $this->plainLabelText((string) ($line['customer_sample_id'] ?? '')))
            ->reject(fn (string $value): bool => $this->isBlankLabelValue($value) || $this->isUuidLike($value))
            ->unique()
            ->values();

        if ($fromLines->isNotEmpty()) {
            return $fromLines->implode(', ');
        }

        $fromForm = $this->firstResolvedLabelValue(
            $instance,
            ['customer_sample_id', 'client_sample_id', 'sample_id'],
            (string) ($formData['customer_sample_id'] ?? $formData['client_sample_id'] ?? $formData['sample_id'] ?? '')
        );
        if (! $this->isBlankLabelValue($fromForm) && ! $this->isUuidLike($fromForm)) {
            return $fromForm;
        }

        $sampleCodes = collect()
            ->merge($instance->batches)
            ->merge($instance->sampleSubmissionRequest?->batch ? [$instance->sampleSubmissionRequest->batch] : [])
            ->flatMap(fn ($batch) => $batch->samples ?? [])
            ->map(fn ($sample): string => $this->plainLabelText((string) ($sample->sample_code ?? '')))
            ->reject(fn (string $value): bool => $this->isBlankLabelValue($value) || $this->isUuidLike($value))
            ->unique()
            ->values();

        if ($sampleCodes->isNotEmpty()) {
            return $sampleCodes->implode(', ');
        }

        return 'N/A';
    }

    /**
     * @param  list<array<string, mixed>>  $sampleLines
     * @param  array<string, mixed>  $formData
     */
    private function resolveTestRequirementForLabel(
        SubmissionFormInstance $instance,
        array $sampleLines,
        array $formData
    ): string {
        $fromLines = collect($sampleLines)
            ->map(fn (array $line): string => $this->plainLabelText((string) ($line['parameter_label'] ?? '')))
            ->reject(fn (string $value): bool => $this->isBlankLabelValue($value) || $this->isUuidLike($value))
            ->flatMap(fn (string $value) => preg_split('/\s*,\s*/', $value) ?: [])
            ->map(fn (string $value): string => trim($value))
            ->reject(fn (string $value): bool => $value === '')
            ->unique()
            ->values();

        if ($fromLines->isNotEmpty()) {
            return $fromLines->implode(', ');
        }

        $rawParameters = $this->plainLabelText((string) ($formData['parameters'] ?? ''));
        if (! $this->isBlankLabelValue($rawParameters) && ! $this->isUuidLike($rawParameters)) {
            $tokens = collect(preg_split('/\s*,\s*/', $rawParameters) ?: [])
                ->map(fn (string $token): string => trim($token))
                ->reject(fn (string $token): bool => $token === '' || $this->isUuidLike($token))
                ->unique()
                ->values();

            if ($tokens->isNotEmpty()) {
                return $tokens->implode(', ');
            }
        }

        return $this->firstResolvedLabelValue(
            $instance,
            ['parameters', 'parameter_requested', 'test_requirement', 'tests_required', 'analysis_elements_select'],
            (string) ($formData['parameter_requested'] ?? $formData['test_requirement'] ?? '')
        );
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function resolveCollectionDateTime(array $formData): string
    {
        $date = trim((string) ($formData['date_of_sampling'] ?? $formData['sampling_date'] ?? $formData['collection_date'] ?? ''));
        $time = trim((string) ($formData['sampling_time'] ?? $formData['collection_time'] ?? ''));

        if ($date !== '' && $time !== '') {
            return $date.' '.$time;
        }

        if ($date !== '') {
            return $date;
        }

        return now()->format('Y-m-d H:i');
    }

    private function plainLabelText(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        $decoded = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = trim(preg_replace('/\s+/u', ' ', strip_tags($decoded)) ?? '');

        return $plain;
    }

    private function isBlankLabelValue(?string $value): bool
    {
        $value = trim((string) $value);

        return $value === '' || strcasecmp($value, 'N/A') === 0;
    }

    private function isUuidLike(?string $value): bool
    {
        $value = trim((string) $value);

        return $value !== '' && Str::isUuid($value);
    }
}