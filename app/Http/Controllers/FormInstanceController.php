<?php

namespace App\Http\Controllers;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class FormInstanceController extends Controller
{
    /**
     * Display a listing of form instances
     */
    public function index(Request $request): View
    {
        $query = SubmissionFormInstance::with(['submissionForm', 'submittedBy']);

        // Filter by form if specified
        if ($request->filled('form_id')) {
            $query->where('submission_form_id', $request->get('form_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('submissionForm', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('submittedBy', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        $instances = $query->orderBy('created_at', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        $forms = SubmissionForm::where('is_published', true)
                              ->orderBy('name')
                              ->get(['id', 'name']);

        return view('form-instances.index', compact('instances', 'forms'));
    }

    /**
     * Show the form for creating a new instance (public form view)
     */
    public function create(SubmissionForm $submissionForm): View
    {
        // Check if form is published
        if (!$submissionForm->is_published) {
            abort(404, 'Form not found or not available.');
        }

        // Load form structure
        $submissionForm->load([
            'sections' => function($query) {
                $query->orderBy('order_index');
            },
            'sections.elementHolders' => function($query) {
                $query->orderBy('order_index');
            },
            'sections.elementHolders.elements' => function($query) {
                $query->orderBy('order_index');
            }
        ]);

        return view('form-instances.create', compact('submissionForm'));
    }

    /**
     * Store a newly created instance
     */
    public function store(Request $request, SubmissionForm $submissionForm): RedirectResponse
    {
        // Check if form is published
        if (!$submissionForm->is_published) {
            abort(404, 'Form not found or not available.');
        }

        // Load form structure for validation
        $submissionForm->load([
            'sections.elementHolders.elements'
        ]);

        // Build validation rules dynamically
        $rules = [];
        $messages = [];
        
        foreach ($submissionForm->sections as $section) {
            if ($section->isRowsSection()) {
                // Handle rows section - array fields
                $templateHolder = $section->getTemplateElementHolder();
                if ($templateHolder) {
                    foreach ($templateHolder->elements as $element) {
                        $fieldName = "field_{$element->id}";
                        $elementRules = [];
                        
                        // Add required rule if element is required
                        if ($element->is_required) {
                            $elementRules[] = 'required';
                            $elementRules[] = 'array';
                            $elementRules[] = 'min:1';
                            $messages["{$fieldName}.required"] = "The {$element->label} field is required.";
                            $messages["{$fieldName}.array"] = "The {$element->label} field must be an array.";
                            $messages["{$fieldName}.min"] = "At least one {$element->label} entry is required.";
                        } else {
                            $elementRules[] = 'nullable';
                            $elementRules[] = 'array';
                        }
                        
                        // Add type-specific validation rules for array elements
                        $this->addElementValidationRules($element, $elementRules, $messages, $fieldName, true);
                        
                        if (!empty($elementRules)) {
                            $rules[$fieldName] = $elementRules;
                        }
                    }
                }
            } else {
                // Handle regular section - single fields
                foreach ($section->elementHolders as $holder) {
                    foreach ($holder->elements as $element) {
                        $fieldName = "field_{$element->id}";
                        $elementRules = [];
                        
                        // Add required rule if element is required
                        if ($element->is_required) {
                            $elementRules[] = 'required';
                            $messages["{$fieldName}.required"] = "The {$element->label} field is required.";
                        }
                        
                        // Add type-specific validation rules
                        $this->addElementValidationRules($element, $elementRules, $messages, $fieldName, false);
                        
                        if (!empty($elementRules)) {
                            $rules[$fieldName] = $elementRules;
                        }
                    }
                }
            }
        }

        // Validate the request
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the form instance
            $instance = SubmissionFormInstance::create([
                'submission_form_id' => $submissionForm->id,
                'submitted_by' => Auth::id(),
                'status' => 'submitted',
                'submitted_at' => now(),
                'data' => [] // Will be populated with processed values
            ]);

            $processedData = [];

            // Process and store field values
            foreach ($submissionForm->sections as $section) {
                if ($section->isRowsSection()) {
                    // Handle rows section - array fields
                    $templateHolder = $section->getTemplateElementHolder();
                    if ($templateHolder) {
                        foreach ($templateHolder->elements as $element) {
                            $fieldName = "field_{$element->id}";
                            $values = $request->input($fieldName, []);
                            
                            if (!empty($values) && is_array($values)) {
                                // Process each row value
                                $processedValues = [];
                                foreach ($values as $index => $value) {
                                    if ($value !== null && $value !== '') {
                                        // Handle file uploads for array fields
                                        if ($element->element_type === 'file' && $request->hasFile($fieldName . '.' . $index)) {
                                            $file = $request->file($fieldName . '.' . $index);
                                            $path = $file->store('form-submissions', 'public');
                                            $value = [
                                                'original_name' => $file->getClientOriginalName(),
                                                'path' => $path,
                                                'size' => $file->getSize(),
                                                'mime_type' => $file->getMimeType()
                                            ];
                                        }
                                        
                                        $processedValues[] = $value;
                                    }
                                }
                                
                                if (!empty($processedValues)) {
                                    // Store the array of values
                                    SubmissionFormInstanceValue::create([
                                        'submission_form_instance_id' => $instance->id,
                                        'submission_form_element_id' => $element->id,
                                        'value' => json_encode($processedValues)
                                    ]);
                                    
                                    $processedData[$element->name] = $processedValues;
                                }
                            }
                        }
                    }
                } else {
                    // Handle regular section - single fields
                    foreach ($section->elementHolders as $holder) {
                        foreach ($holder->elements as $element) {
                            $fieldName = "field_{$element->id}";
                            $value = $request->input($fieldName);
                            
                            if ($value !== null) {
                                // Handle file uploads
                                if ($element->element_type === 'file' && $request->hasFile($fieldName)) {
                                    $file = $request->file($fieldName);
                                    $path = $file->store('form-submissions', 'public');
                                    $value = [
                                        'original_name' => $file->getClientOriginalName(),
                                        'path' => $path,
                                        'size' => $file->getSize(),
                                        'mime_type' => $file->getMimeType()
                                    ];
                                }
                                
                                // Store the field value
                                SubmissionFormInstanceValue::create([
                                    'submission_form_instance_id' => $instance->id,
                                    'submission_form_element_id' => $element->id,
                                    'value' => is_array($value) ? json_encode($value) : $value
                                ]);
                                
                                $processedData[$element->name] = $value;
                            }
                        }
                    }
                }
            }

            // Update instance with processed data
            $instance->update(['data' => $processedData]);

            DB::commit();

            return redirect()->route('form-instances.success', $instance)
                           ->with('success', 'Form submitted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                           ->with('error', 'An error occurred while submitting the form. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Add element-specific validation rules
     */
    private function addElementValidationRules($element, &$elementRules, &$messages, $fieldName, $isArray = false)
    {
        $arrayPrefix = $isArray ? '.*' : '';
        
        // Add type-specific validation rules
        switch ($element->element_type) {
            case 'email':
                $elementRules[] = 'email' . $arrayPrefix;
                $messages["{$fieldName}.email"] = "The {$element->label} must be a valid email address.";
                break;
            case 'number':
                $elementRules[] = 'numeric' . $arrayPrefix;
                $messages["{$fieldName}.numeric"] = "The {$element->label} must be a number.";
                break;
            case 'date':
                $elementRules[] = 'date' . $arrayPrefix;
                $messages["{$fieldName}.date"] = "The {$element->label} must be a valid date.";
                break;
            case 'file':
                $elementRules[] = 'file' . $arrayPrefix;
                if (!empty($element->validation_rules['max_size'])) {
                    $maxSize = $element->validation_rules['max_size'];
                    $elementRules[] = "max:{$maxSize}" . $arrayPrefix;
                    $messages["{$fieldName}.max"] = "The {$element->label} may not be greater than {$maxSize} kilobytes.";
                }
                if (!empty($element->validation_rules['allowed_types'])) {
                    $types = implode(',', $element->validation_rules['allowed_types']);
                    $elementRules[] = "mimes:{$types}" . $arrayPrefix;
                    $messages["{$fieldName}.mimes"] = "The {$element->label} must be a file of type: {$types}.";
                }
                break;
        }
        
        // Add custom validation rules from element settings
        if (!empty($element->validation_rules['min_length'])) {
            $elementRules[] = 'min:' . $element->validation_rules['min_length'] . $arrayPrefix;
        }
        if (!empty($element->validation_rules['max_length'])) {
            $elementRules[] = 'max:' . $element->validation_rules['max_length'] . $arrayPrefix;
        }
    }

    /**
     * Display the specified instance
     */
    public function show(SubmissionFormInstance $instance): View
    {
        $instance->load([
            'submissionForm',
            'submittedBy',
            'values.element.holder.section',
            'reviewedBy',
            'approvedBy'
        ]);

        return view('form-instances.show', compact('instance'));
    }

    /**
     * Show success page after form submission
     */
    public function success(SubmissionFormInstance $instance): View
    {
        // Only allow viewing success page for the user who submitted or admins
        if ($instance->submitted_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403);
        }

        $instance->load(['submissionForm']);

        return view('form-instances.success', compact('instance'));
    }

    /**
     * Update instance status (for review/approval workflow)
     */
    public function updateStatus(Request $request, SubmissionFormInstance $instance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:submitted,under_review,approved,rejected',
            'review_notes' => 'nullable|string|max:1000'
        ]);

        $updateData = [
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null
        ];

        // Set appropriate user and timestamp based on status
        switch ($validated['status']) {
            case 'under_review':
                $updateData['reviewed_by'] = Auth::id();
                $updateData['reviewed_at'] = now();
                break;
            case 'approved':
            case 'rejected':
                $updateData['approved_by'] = Auth::id();
                $updateData['approved_at'] = now();
                break;
        }

        $instance->update($updateData);

        return redirect()->back()
                       ->with('success', 'Instance status updated successfully.');
    }

    /**
     * Export instance data
     */
    public function export(SubmissionFormInstance $instance)
    {
        $instance->load([
            'submissionForm',
            'submittedBy',
            'values.element'
        ]);

        $data = [
            'form_name' => $instance->submissionForm->name,
            'submitted_by' => $instance->submittedBy->name ?? 'Unknown',
            'submitted_at' => $instance->submitted_at->format('Y-m-d H:i:s'),
            'status' => $instance->status,
            'values' => []
        ];

        foreach ($instance->values as $value) {
            $data['values'][$value->element->label] = $value->value;
        }

        $filename = "form-submission-{$instance->id}-" . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($data)
                       ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}