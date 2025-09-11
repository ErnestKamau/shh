<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\Models\SubmissionFormElement;
use App\SampleHeader;
use App\SampleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FormInstanceController extends Controller
{
    /**
     * Display a listing of form instances for the current user
     */
    public function index(Request $request)
    {
        $query = SubmissionFormInstance::with(['submissionForm', 'submittedBy'])
            ->submittedBy(auth()->id())
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
            $query->where(function($q) use ($search) {
                $q->where('form_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('submissionForm', function($q) use ($search) {
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
        // Check if user can create instances for this form
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is not available for submission.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
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

        // Determine if this is a public submission
        $isPublicSubmission = !auth()->check();
        $submittedBy = $isPublicSubmission ? null : auth()->id();

        // Generate form number and create instance in a transaction with retry logic
        Log::info('Creating form instance', [
            'form_id' => $submissionForm->id,
            'form_name' => $submissionForm->name,
            'is_public' => $isPublicSubmission
        ]);
        
        $instance = $this->createFormInstanceWithRetry($submissionForm, $request, $submittedBy);

        // Log the creation (only if user is authenticated)
        if (!$isPublicSubmission) {
            $instance->logAction('created', auth()->user());
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
     * Display the form for filling out
     */
    public function fill(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Check if user owns this instance
        if ($instance->submitted_by !== auth()->id()) {
            abort(403, 'You are not authorized to access this form instance.');
        }

        // Check if form is still available
        if (!$submissionForm->isPublishedAndActive()) {
            return redirect()->back()->with('error', 'This form is no longer available for submission.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Load existing values
        $existingValues = $instance->values()->with('element')->get()->keyBy('submission_form_element_id');

        return view('submission-forms.instances.fill', compact('submissionForm', 'instance', 'existingValues'));
    }

    /**
     * Update the form instance with submitted data
     */
    public function update(Request $request, SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
      
        // Check if user owns this instance
        if ($instance->submitted_by !== auth()->id()) {
            abort(403, 'You are not authorized to update this form instance.');
        }
        // Check if instance can be updated
        if (!$instance->isDraft()) {
            return redirect()->back()->with('error', 'This form instance cannot be updated.');
        }

        // Load form elements for validation
        $elements = SubmissionFormElement::whereHas('holder.section', function($query) use ($submissionForm) {
            $query->where('submission_form_id', $submissionForm->id);
        })->get();


        // Build validation rules
        $validationRules = $this->buildValidationRules($elements, $request);
       

        // Validate the request
        $validator = Validator::make($request->all(), $validationRules);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please correct the errors below.');
        }

        DB::beginTransaction();
        try {
            // dd("here");
            $this->processFormData($instance, $request, $elements);
            Log::info('Processing form data');
            // Update instance status if submitting
            if ($request->input('action') === 'submit') {
                
                $instance->submit(auth()->user());
            } else {
                // Log as updated for draft saves
                $instance->logAction('updated', auth()->user());
            }

            DB::commit();

            if ($request->input('action') === 'submit') {
                return redirect()->route('submission-forms.instances.show', [
                    'submissionForm' => $submissionForm,
                    'instance' => $instance
                ])->with('success', 'Form submitted successfully!');
            } else {
                return redirect()->back()->with('success', 'Form saved as draft.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating form instance: ' . $e->getMessage());
            dd("here error");
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the form. Please try again.');
        }
    }

    /**
     * Display the specified form instance
     */
    public function show(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Check if user can view this instance
        if ($instance->submitted_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'You are not authorized to view this form instance.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Load existing values
        $existingValues = $instance->values()->with('element')->get()->keyBy('submission_form_element_id');

        // Load audit trail
        $auditLogs = $instance->auditLogs()->with('user')->latest()->get();

        return view('submission-forms.instances.show', compact('submissionForm', 'instance', 'existingValues', 'auditLogs'));
    }

    /**
     * Show the form for editing a draft instance
     */
    public function edit(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Check if user owns this instance
        if ($instance->submitted_by !== auth()->id()) {
            abort(403, 'You are not authorized to edit this form instance.');
        }

        // Check if instance can be edited
        if (!$instance->isDraft()) {
            return redirect()->back()->with('error', 'This form instance cannot be edited.');
        }

        // Load form with all relationships
        $submissionForm->load([
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Load existing values
        $existingValues = $instance->values()->with('element')->get()->keyBy('submission_form_element_id');

        return view('submission-forms.instances.edit', compact('submissionForm', 'instance', 'existingValues'));
    }

    /**
     * Delete a draft instance
     */
    public function destroy(SubmissionForm $submissionForm, SubmissionFormInstance $instance)
    {
        // Check if user owns this instance
        if ($instance->submitted_by !== auth()->id()) {
            abort(403, 'You are not authorized to delete this form instance.');
        }

        // Check if instance can be deleted
        if (!$instance->isDraft()) {
            return redirect()->back()->with('error', 'Only draft instances can be deleted.');
        }

        $instance->delete();

        return redirect()->route('submission-forms.instances.index')
            ->with('success', 'Form instance deleted successfully.');
    }

    /**
     * Generate a unique form number with automatic increment on duplicates
     */
    private function generateFormNumber(SubmissionForm $form): string
    {
        $prefix = $form->naming_convention_prefix ?? 'SF';
        $format = $form->naming_convention_format ?? '{prefix}/{year}/{sequence}';
        $year = date('Y');
        
        // Get the highest sequence number globally for this prefix and year
        $lastInstance = SubmissionFormInstance::where('form_number', 'LIKE', "{$prefix}%/{$year}/%")
            ->orderBy('form_number', 'desc')
            ->first();
            
        $sequence = 1;
        if ($lastInstance) {
            $parts = explode('/', $lastInstance->form_number);
            $lastSequence = intval(end($parts));
            $sequence = $lastSequence + 1;
        }
        
        Log::info('Form number generation start', [
            'form_id' => $form->id,
            'prefix' => $prefix,
            'year' => $year,
            'last_instance' => $lastInstance ? $lastInstance->form_number : 'None',
            'starting_sequence' => $sequence
        ]);
        
        $maxAttempts = 100; // Increased to handle more concurrent requests
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Generate the form number
            $formNumber = str_replace(
                ['{prefix}', '{year}', '{sequence}'],
                [$prefix, $year, str_pad($sequence, 3, '0', STR_PAD_LEFT)],
                $format
            );
            
            // Check if this form number already exists (globally, not just for this form)
            $exists = SubmissionFormInstance::where('form_number', $formNumber)->exists();
            
            if (!$exists) {
                Log::info('Generated unique form number', [
                    'form_id' => $form->id,
                    'form_number' => $formNumber,
                    'attempt' => $attempt
                ]);
                return $formNumber;
            }
            
            // If it exists, automatically increment sequence and try again
            Log::warning('Form number collision detected, auto-incrementing', [
                'form_id' => $form->id,
                'collision_number' => $formNumber,
                'attempt' => $attempt,
                'next_sequence' => $sequence + 1
            ]);
            
            $sequence++;
            
        } while ($attempt < $maxAttempts);
        
        // If we still can't find a unique number after many attempts, use timestamp as fallback
        $timestamp = time();
        $fallbackNumber = str_replace(
            ['{prefix}', '{year}', '{sequence}'],
            [$prefix, $year, str_pad($timestamp, 6, '0', STR_PAD_LEFT)],
            $format
        );
        
        Log::error('Failed to generate unique form number after maximum attempts, using timestamp fallback', [
            'form_id' => $form->id,
            'fallback_number' => $fallbackNumber,
            'attempts' => $maxAttempts
        ]);
        
        return $fallbackNumber;
    }

    /**
     * Create form instance with retry logic for duplicate form numbers
     */
    private function createFormInstanceWithRetry(SubmissionForm $submissionForm, Request $request, $submittedBy): SubmissionFormInstance
    {
        $maxRetries = 5;
        $retryCount = 0;
        
        do {
            $retryCount++;
            
            try {
                return DB::transaction(function () use ($submissionForm, $request, $submittedBy) {
                    // Generate form number
                    $formNumber = $this->generateFormNumber($submissionForm);

                    // Create the instance
                    return SubmissionFormInstance::create([
                        'submission_form_id' => $submissionForm->id,
                        'form_number' => $formNumber,
                        'title' => $request->input('title'),
                        'submitted_by' => $submittedBy,
                        'status' => 'draft',
                        'priority' => $request->input('priority', 'normal'),
                        'due_date' => $request->input('due_date')
                    ]);
                });
                
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if it's a duplicate key error
                if ($e->getCode() == 23000 && strpos($e->getMessage(), 'submission_form_instances_form_number_unique') !== false) {
                    Log::warning('Duplicate form number detected during creation, retrying', [
                        'form_id' => $submissionForm->id,
                        'retry_count' => $retryCount,
                        'error' => $e->getMessage()
                    ]);
                    
                    if ($retryCount >= $maxRetries) {
                        Log::error('Maximum retries reached for form instance creation', [
                            'form_id' => $submissionForm->id,
                            'retries' => $maxRetries
                        ]);
                        throw $e;
                    }
                    
                    // Small delay before retry to reduce collision probability
                    usleep(100000); // 100ms delay
                    continue;
                }
                
                // If it's not a duplicate key error, re-throw
                throw $e;
            }
            
        } while ($retryCount < $maxRetries);
        
        // This should never be reached, but just in case
        throw new \Exception('Failed to create form instance after maximum retries');
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
            }
            
            // Custom validation rules
            if ($element->validation_rules) {
                $elementRules = array_merge($elementRules, $element->validation_rules);
            }
            
            // Handle array fields (from rows sections)
            if ($request->has($fieldName) && is_array($request->input($fieldName))) {
                $rules[$fieldName . '.*'] = $elementRules;
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
            $value = $request->input($fieldName);
            
            // Handle array fields (from rows sections)
            if (is_array($value)) {
                $this->processArrayField($instance, $element, $value);
            } else {
                $this->processSingleField($instance, $element, $value, $request);
            }
        }
    }

    /**
     * Process a single field value
     */
    private function processSingleField(SubmissionFormInstance $instance, SubmissionFormElement $element, $value, Request $request): void
    {
        // Handle file uploads
        if ($element->element_type === 'file' && $request->hasFile($element->name)) {
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
    private function processArrayField(SubmissionFormInstance $instance, SubmissionFormElement $element, array $values): void
    {
        // Delete existing values for this element
        $instance->values()->where('submission_form_element_id', $element->id)->delete();
        
        // Save each value with array index
        foreach ($values as $index => $value) {
            if ($value !== null && $value !== '') {
                $this->saveFieldValue($instance, $element, $value, null, $index);
            }
        }
    }

    /**
     * Save a field value to the database
     */
    private function saveFieldValue(SubmissionFormInstance $instance, SubmissionFormElement $element, $value, $filePath = null, $arrayIndex = null): void
    {
        // Create or update the value
        $instanceValue = SubmissionFormInstanceValue::updateOrCreate(
            [
                'submission_form_instance_id' => $instance->id,
                'submission_form_element_id' => $element->id,
                'array_index' => $arrayIndex
            ],
            [
                'value' => $value,
                'file_path' => $filePath
            ]
        );

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
        
        if (!auth()->check() && !in_array($elementType, $publicElementTypes)) {
            Log::warning('Unauthenticated request to dynamic options for restricted element type', ['element_type' => $elementType]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $clientId = $request->get('client_id');
        $sampleTypeId = $request->get('sample_type_id');
        $storeId = $request->get('store_id');
        $clientUnitId = $request->get('client_unit_id');

        Log::info('Dynamic options request', [
            'element_type' => $elementType,
            'client_id' => $clientId,
            'client_unit_id' => $clientUnitId,
            'sample_type_id' => $sampleTypeId,
            'store_id' => $storeId
        ]);

        try {
            $options = [];

            switch ($elementType) {
                case 'client_select':
                    $clients = \App\Models\CRM\CRMCustomer::where('active', 1)
                        ->where('company_id', getUserCompany())
                        ->orderBy('name')
                        ->get();

                    foreach ($clients as $client) {
                        $options[] = [
                            'id' => $client->id,
                            'text' => $client->name
                        ];
                    }
                    break;

                case 'sample_type_select':
                    $options = \App\SampleType::select('id', 'name as text')
                        ->get()
                        ->toArray();
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
                    Log::info('Analysis elements request', [
                        'analysis_type_id' => $analysisTypeId,
                        'request_data' => $request->all()
                    ]);
                    
                    if ($analysisTypeId) {
                        $elements = \App\AnalysisElements::where('analysis_type_id', $analysisTypeId)
                            ->with('analyte')
                            ->get();
                            
                        Log::info('Found analysis elements', [
                            'count' => $elements->count(),
                            'elements' => $elements->toArray()
                        ]);
                        
                        $options = $elements->map(function($element) {
                            // Get parameter name from the relationship or fallback
                            $parametername = 'Unknown Parameter';
                            if ($element->analyte) {
                                $parametername = $element->analyte->name ?? 'Unknown Parameter';
                            } elseif ($element->analyte_id) {
                                // Fallback: try to get the name directly
                                $analyte = \App\Analyte::find($element->analyte_id);
                                $parametername = $analyte ? $analyte->name : 'Unknown Parameter';
                            }
                            
                            $method = $element->method ?? 'No Method';
                            return [
                                'id' => $element->id,
                                'text' => $parametername . ' (' . $method . ')'
                            ];
                        })->toArray();
                        
                        Log::info('Mapped options', ['options' => $options]);
                    } else {
                        $options = [];
                        Log::info('No analysis_type_id provided');
                    }
                    break;

                case 'standard_select':
                    $options = \App\Standards::select('id', 'name as text')
                        ->get()
                        ->toArray();
                    break;

                case 'sample_point_select':
                    if ($clientUnitId) {
                        $options = \App\Models\CRM\SamplePoint::where('crm_company_unit_id', $clientUnitId)
                            ->select('id', 'name as text')
                            ->get()
                            ->toArray();
                    }
                    break;

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
}