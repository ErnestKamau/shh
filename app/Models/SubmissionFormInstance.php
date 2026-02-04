<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubmissionFormInstance extends Model
{

    protected $fillable = [
        'submission_form_id',
        'form_number',
        'sequence_number',
        'title',
        'submitted_by',
        'status',
        'priority',
        'due_date',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'due_date' => 'date'
    ];

    /**
     * Get the submission form that this instance belongs to
     */
    public function submissionForm(): BelongsTo
    {
        return $this->belongsTo(SubmissionForm::class);
    }

    /**
     * Get the user who submitted this instance
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who reviewed this instance
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the values for this instance
     */
    public function values(): HasMany
    {
        return $this->hasMany(SubmissionFormInstanceValue::class);
    }

    /**
     * Get the audit logs for this instance
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(SubmissionFormAuditLog::class);
    }

    /**
     * Get all batches (sample headers) linked to this form instance
     */
    public function batches(): HasMany
    {
        return $this->hasMany(\App\SampleHeader::class, 'submission_form_instance_id');
    }

    /**
     * Generate a unique form number based on the form's naming convention
     */
    public function generateFormNumber(): string
    {
        $result = \App\Services\FormNumberGenerator::generate($this->submissionForm);
        return $result['format'];
    }

    /**
     * Get a value by element name
     */
    public function getValueByElementName(string $elementName)
    {
        $value = $this->values()
            ->whereHas('element', function ($query) use ($elementName) {
                $query->where('name', $elementName);
            })->first();

        return $value ? $value->value : null;
    }

    /**
     * Resolve a display value by element name
     */
    public function resolveDisplayValueByName(string $elementName)
    {
        $instanceValue = $this->values()
            ->with('element')
            ->whereHas('element', function ($query) use ($elementName) {
                $query->where('name', $elementName);
            })->first();

        if (!$instanceValue || !$instanceValue->element) {
            return null;
        }

        return $this->resolveDisplayValue($instanceValue->element, $instanceValue->value);
    }

    /**
     * Get all values as an associative array with element names as keys
     */
    public function getValuesArray(): array
    {
        $valuesArray = [];

        $this->values()->with('element')->get()->each(function ($value) use (&$valuesArray) {
            $valuesArray[$value->element->name] = $value->getFormattedValue();
        });

        return $valuesArray;
    }

    /**
     * Check if this instance is overdue
     */
    public function isOverdue(): bool
    {
        return $this->due_date &&
            $this->due_date->isPast() &&
            !in_array($this->status, ['approved', 'rejected', 'cancelled']);
    }

    /**
     * Check if this instance is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if this instance has been submitted
     */
    public function isSubmitted(): bool
    {
        return in_array($this->status, ['submitted', 'in_review', 'approved', 'rejected']);
    }

    /**
     * Check if this instance is pending review
     */
    public function isPendingReview(): bool
    {
        return in_array($this->status, ['submitted', 'in_review']);
    }

    /**
     * Check if this instance has been completed (approved or rejected)
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['approved', 'rejected']);
    }

    /**
     * Log an action for this instance
     */
    public function logAction(string $action, $user, ?array $fieldChanges = null, ?string $notes = null): void
    {
        $this->auditLogs()->create([
            'user_id' => $user->id,
            'action' => $action,
            'field_changes' => $fieldChanges,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Submit this instance
     */
    public function submit($user): bool
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);

        $this->logAction('submitted', $user);

        return true;
    }

    /**
     * Approve this instance
     */
    public function approve($user, ?string $notes = null): bool
    {
        if (!$this->isPendingReview()) {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'review_notes' => $notes
        ]);

        $this->logAction('approved', $user, null, $notes);

        return true;
    }

    /**
     * Reject this instance
     */
    public function reject($user, ?string $notes = null): bool
    {
        if (!$this->isPendingReview()) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'review_notes' => $notes
        ]);

        $this->logAction('rejected', $user, null, $notes);

        return true;
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusBadgeColor(): string
    {
        switch ($this->status) {
            case 'draft':
                return 'secondary';
            case 'in_review':
                return 'warning';
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            case 'cancelled':
                return 'dark';
            default:
                return 'secondary';
        }
    }

    /**
     * Get priority badge color for UI
     */
    public function getPriorityBadgeColor(): string
    {
        switch ($this->priority) {
            case 'low':
                return 'success';
            case 'normal':
                return 'info';
            case 'high':
                return 'warning';
            case 'urgent':
                return 'danger';
            default:
                return 'info';
        }
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by priority
     */
    public function scopeWithPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope to filter overdue instances
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['approved', 'rejected', 'cancelled']);
    }

    /**
     * Scope to filter by submitted user
     */
    public function scopeSubmittedBy($query, int $userId)
    {
        return $query->where('submitted_by', $userId);
    }

    /**
     * Get all form fields with their values and mapping information
     * Returns an array of sample batches grouped by unique sample_type values
     */
    public function getAllFieldsWithValues()
    {
        // Load the form with all relationships
        $this->load([
            'submissionForm.sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Separate elements by mapping table/section
        $sampleHeaderElements = [];
        $sampleDetailElements = [];
        $sampleTypeElement = null;

        foreach ($this->submissionForm->sections as $section) {
            $isRowsSection = $section->isRowsSection();

            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    if ($element->is_mapped && $element->mapping_table && $element->mapping_field) {
                        if ($element->mapping_table === 'sample_headers') {
                            $sampleHeaderElements[] = $element;
                            if ($element->mapping_field === 'sample_type_id' && !$sampleTypeElement) {
                                $sampleTypeElement = $element;
                            }
                            continue;
                        }

                        if ($element->mapping_table === 'sample_details') {
                            $sampleDetailElements[] = $element;
                            continue;
                        }
                    }

                    // Fallback: rows sections without explicit mapping should still be treated as sample detail fields
                    if ($isRowsSection && $element->isInputField()) {
                        $sampleDetailElements[] = $element;
                    }
                }
            }
        }

        // Get all values for sample_type_id element (from rows-section)
        $sampleTypeValues = collect();
        if ($sampleTypeElement) {
            $sampleTypeValues = $this->values()
                ->where('submission_form_element_id', $sampleTypeElement->id)
                ->orderBy('array_index')
                ->get();
        }

        // If no sample_type_id values found, create a single batch
        if ($sampleTypeValues->isEmpty()) {
            return [$this->createSampleBatch($sampleHeaderElements, $sampleDetailElements, null)];
        }

        // Group by unique sample_type_id values
        $uniqueSampleTypes = $sampleTypeValues->pluck('value')->unique()->filter();
        $sampleBatches = [];

        foreach ($uniqueSampleTypes as $sampleTypeId) {
            // Get array indexes for this sample type
            $arrayIndexes = $sampleTypeValues->where('value', $sampleTypeId)->pluck('array_index')->toArray();

            $sampleBatches[] = $this->createSampleBatch(
                $sampleHeaderElements,
                $sampleDetailElements,
                $sampleTypeId,
                $arrayIndexes
            );
        }

        return $sampleBatches;
    }

    /**
     * Create a sample batch with sample_header and sample_details
     */
    private function createSampleBatch($sampleHeaderElements, $sampleDetailElements, $sampleTypeId = null, $arrayIndexes = [])
    {
        $sampleHeader = [];
        $sampleDetails = [];

        // Process sample header elements
        foreach ($sampleHeaderElements as $element) {
            $elementValues = $this->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            // For sample header elements, we typically want the first value or a single value
            // unless it's the sample_type_id which should be filtered by array indexes
            if ($element->mapping_field === 'sample_type_id' && !empty($arrayIndexes)) {
                // For sample_type_id, get the first value from the filtered indexes
                $elementValues = $elementValues->whereIn('array_index', $arrayIndexes);
            }

            $value = $this->getElementValue($element, $elementValues);

            if ($value !== null) {
                $sampleHeader[$element->mapping_field] = $value;
            }
        }

        // Override sample_type_id if provided
        if ($sampleTypeId !== null) {
            $sampleHeader['sample_type_id'] = $sampleTypeId;
        }

        // Process sample detail elements
        foreach ($sampleDetailElements as $element) {
            $elementValues = $this->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            // If we have specific array indexes, filter by them
            if (!empty($arrayIndexes)) {
                $elementValues = $elementValues->whereIn('array_index', $arrayIndexes);
            }

            // Group values by array_index to create separate detail records
            $groupedValues = $elementValues->groupBy('array_index');

            foreach ($groupedValues as $index => $values) {
                $value = $this->getElementValue($element, $values);

                if ($value !== null) {
                    // Initialize detail record if not exists
                    if (!isset($sampleDetails[$index])) {
                        $sampleDetails[$index] = [];
                    }
                    $fieldKey = $this->resolveFieldKey($element);
                    $sampleDetails[$index][$fieldKey] = $value;
                }
            }
        }

        // Convert indexed array to sequential array and ensure proper ordering
        $sampleDetails = array_values($sampleDetails);

        // Ensure sample_header has required fields
        if (empty($sampleHeader)) {
            $sampleHeader = ['sample_type_id' => $sampleTypeId];
        }

        return [
            'sample_header' => $sampleHeader,
            'sample_details' => $sampleDetails
        ];
    }

    /**
     * Resolve the field key to use when building sample detail rows
     */
    private function resolveFieldKey($element): string
    {
        $fieldKey = $element->mapping_field ?: $element->name ?: 'field_' . $element->id;

        $aliases = [
            'sample_quantity' => 'quantity',
        ];

        return $aliases[$fieldKey] ?? $fieldKey;
    }

    /**
     * Get the appropriate value for an element
     */
    private function getElementValue($element, $elementValues)
    {
        if ($elementValues->isEmpty()) {
            return $element->default_value;
        }

        // For single values, return the first value
        if ($elementValues->count() === 1) {
            $value = $elementValues->first()->value;
            return $value !== null && $value !== '' ? $value : $element->default_value;
        }

        // For multiple values, return as array
        $values = $elementValues->pluck('value')->filter(function ($value) {
            return $value !== null && $value !== '';
        })->values()->toArray();

        return !empty($values) ? $values : $element->default_value;
    }

    /**
     * Get all sample batches (alias for getAllFieldsWithValues)
     */
    public function getSampleBatches()
    {
        return $this->getAllFieldsWithValues();
    }

    /**
     * Get the first sample batch (for single sample scenarios)
     */
    public function getFirstSampleBatch()
    {
        $batches = $this->getAllFieldsWithValues();
        return $batches[0] ?? null;
    }

    /**
     * Get sample batches by sample type ID
     */
    public function getSampleBatchesByType(string $sampleTypeId)
    {
        $batches = $this->getAllFieldsWithValues();
        return array_filter($batches, function ($batch) use ($sampleTypeId) {
            return ($batch['sample_header']['sample_type_id'] ?? null) == $sampleTypeId;
        });
    }

    /**
     * Get all unique sample type IDs
     */
    public function getUniqueSampleTypes()
    {
        $batches = $this->getAllFieldsWithValues();
        $sampleTypeIds = [];

        foreach ($batches as $batch) {
            $sampleTypeId = $batch['sample_header']['sample_type_id'] ?? null;
            if ($sampleTypeId && !in_array($sampleTypeId, $sampleTypeIds)) {
                $sampleTypeIds[] = $sampleTypeId;
            }
        }

        return $sampleTypeIds;
    }

    /**
     * Get total number of sample batches
     */
    public function getSampleBatchCount()
    {
        return count($this->getAllFieldsWithValues());
    }

    /**
     * Get all sample details across all batches
     */
    public function getAllSampleDetails()
    {
        $batches = $this->getAllFieldsWithValues();
        $allDetails = [];

        foreach ($batches as $batch) {
            $allDetails = array_merge($allDetails, $batch['sample_details']);
        }

        return $allDetails;
    }

    /**
     * Get sample details for a specific sample type ID
     */
    public function getSampleDetailsByType(string $sampleTypeId)
    {
        $batches = $this->getSampleBatchesByType($sampleTypeId);
        $details = [];

        foreach ($batches as $batch) {
            $details = array_merge($details, $batch['sample_details']);
        }

        return $details;
    }

    /**
     * Get structured form data for display with enhanced metadata
     * Returns data optimized for frontend consumption with dependency information
     */
    public function getFormDataForDisplay()
    {
        // Load the form with all relationships
        $this->load([
            'submissionForm.sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        $formData = [
            'sections' => [],
            'elements_metadata' => [],
            'dependency_chain' => []
        ];

        // Process each section
        foreach ($this->submissionForm->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'title' => $section->title,
                'description' => $section->description,
                'section_type' => $section->section_type,
                'element_holders' => []
            ];

            // Process element holders within the section
            foreach ($section->elementHolders as $holder) {
                $holderData = [
                    'id' => $holder->id,
                    'title' => $holder->title,
                    'description' => $holder->description,
                    'holder_type' => $holder->holder_type,
                    'elements' => [],
                    'rows_data' => []
                ];

                // Process elements within the holder
                foreach ($holder->elements as $element) {
                    $elementData = [
                        'id' => $element->id,
                        'name' => $element->name,
                        'label' => $element->label,
                        'element_type' => $element->element_type,
                        'is_required' => $element->is_required,
                        'is_mapped' => $element->is_mapped,
                        'mapping_table' => $element->mapping_table,
                        'mapping_field' => $element->mapping_field,
                        'custom_element_type' => $element->element_type,
                        'options' => $element->options,
                        'default_value' => $element->default_value,
                        'validation_rules' => $element->validation_rules,
                        'dependency_info' => $this->getElementDependencyInfo($element),
                        'saved_values' => []
                    ];

                    // Get saved values for this element
                    $elementValues = $this->values()
                        ->where('submission_form_element_id', $element->id)
                        ->orderBy('array_index')
                        ->get();

                    foreach ($elementValues as $value) {
                        $elementData['saved_values'][] = [
                            'value' => $value->value,
                            'array_index' => $value->array_index ?? 0,
                            'file_path' => $value->file_path,
                            'display_value' => $this->resolveDisplayValue($element, $value->value)
                        ];
                    }

                    // Store element metadata for quick access
                    $formData['elements_metadata'][$element->id] = $elementData;

                    // Add to holder elements
                    $holderData['elements'][] = $elementData;
                }

                // For rows sections, group data by array index
                // Check if this is a rows section based on section type or holder type
                $isRowsSection = $holder->holder_type === 'rows' ||
                    ($section->section_type === 'rows_section' && $this->hasMultipleArrayIndices($holder->elements));

                if ($isRowsSection) {
                    $holderData['holder_type'] = 'rows'; // Override to ensure proper display
                    $holderData['rows_data'] = $this->groupRowsDataByIndex($holder->elements);
                }

                $sectionData['element_holders'][] = $holderData;
            }

            $formData['sections'][] = $sectionData;
        }

        // Build dependency chain
        $formData['dependency_chain'] = $this->buildDependencyChain($formData['elements_metadata']);

        return $formData;
    }

    /**
     * Get dependency information for an element
     */
    private function getElementDependencyInfo($element)
    {
        // Use element_type as the custom type since that's where the actual type is stored
        $customType = $element->element_type;

        $dependencies = [
            'depends_on' => null,
            'dependency_level' => 0,
            'is_independent' => false
        ];

        // Define dependency chain
        switch ($customType) {
            case 'client_select':
            case 'sample_type_select':
            case 'store_select':
            case 'standard_select':
            case 'sample_condition_select':
            case 'user_select':
                $dependencies['is_independent'] = true;
                $dependencies['dependency_level'] = 1;
                break;

            case 'client_unit_select':
            case 'client_contact_select':
                $dependencies['depends_on'] = 'client_select';
                $dependencies['dependency_level'] = 2;
                break;

            case 'sample_point_select':
                $dependencies['depends_on'] = 'client_unit_select';
                $dependencies['dependency_level'] = 3;
                break;

            case 'analysis_type_select':
                $dependencies['depends_on'] = 'sample_type_select';
                $dependencies['dependency_level'] = 2;
                break;

            case 'analysis_elements_select':
                $dependencies['depends_on'] = 'analysis_type_select';
                $dependencies['dependency_level'] = 3;
                break;

            case 'store_slot_select':
                $dependencies['depends_on'] = 'store_select';
                $dependencies['dependency_level'] = 2;
                break;
        }

        return $dependencies;
    }

    /**
     * Check if elements have multiple array indices (indicating rows data)
     */
    private function hasMultipleArrayIndices($elements)
    {
        $arrayIndices = [];

        foreach ($elements as $element) {
            $elementValues = $this->values()
                ->where('submission_form_element_id', $element->id)
                ->pluck('array_index')
                ->toArray();

            $arrayIndices = array_merge($arrayIndices, $elementValues);
        }

        // Check if we have more than one unique array index
        return count(array_unique($arrayIndices)) > 1;
    }

    /**
     * Resolve display value for custom field elements
     * Converts IDs to actual names/labels from the database
     */
    public function resolveDisplayValue($element, $value)
    {
        if (empty($value)) {
            return 'N/A';
        }

        $elementType = $element->element_type;

        // Handle comma-separated values (for multi-select fields)
        if (strpos($value, ',') !== false) {
            $ids = explode(',', $value);
            $resolvedValues = [];

            foreach ($ids as $id) {
                $id = trim($id);
                if (!empty($id)) {
                    $resolvedValues[] = $this->resolveSingleValue($elementType, $id);
                }
            }

            return implode(', ', $resolvedValues);
        }

        return $this->resolveSingleValue($elementType, $value);
    }

    /**
     * Resolve a single ID to its display value
     */
    public function resolveSingleValue($elementType, $id)
    {
        try {
            switch ($elementType) {
                case 'client_select':
                    $client = DB::table('crm_customers')->where('id', $id)->first();
                    return $client ? $client->name : $id;

                case 'client_unit_select':
                    $unit = DB::table('crm_company_units')->where('id', $id)->first();
                    return $unit ? $unit->name : $id;

                case 'company_sub_unit_select':
                    $subUnit = DB::table('crm_company_sub_units')->where('id', $id)->first();
                    return $subUnit ? $subUnit->name : $id;

                case 'sample_type_select':
                    $sampleType = DB::table('sample_types')->where('id', $id)->first();
                    return $sampleType ? $sampleType->name : $id;

                case 'analysis_type_select':
                    $analysisType = DB::table('analysis_types')->where('id', $id)->first();
                    return $analysisType ? $analysisType->name : $id;

                case 'sample_point_select':
                    $samplePoint = DB::table('sample_points')->where('id', $id)->first();
                    return $samplePoint ? $samplePoint->name : $id;

                case 'sample_condition_select':
                    $condition = DB::table('sample_conditions')->where('id', $id)->first();
                    return $condition ? $condition->name : $id;

                case 'standard_select':
                    $standard = DB::table('standards')->where('id', $id)->first();
                    return $standard ? $standard->name : $id;

                case 'store_select':
                    $store = DB::table('inventory_stores')->where('id', $id)->first();
                    return $store ? $store->name : $id;

                case 'store_slot_select':
                    $slot = DB::table('inventory_store_slots')->where('id', $id)->first();
                    return $slot ? $slot->name : $id;

                case 'client_contact_select':
                    $contact = DB::table('crm_customer_contacts')
                        ->where('id', $id)
                        ->first();
                    if ($contact) {
                        $name = trim($contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name);
                        return $name ?: $id;
                    }
                    return $id;

                case 'analysis_elements_select':
                    $element = DB::table('analytes')->where('id', $id)->first();
                    return $element ? $element->name : $id;

                case 'user_select':
                    $user = DB::table('users')->where('id', $id)->first();
                    if ($user) {
                        return $user->name ?: $user->email ?: $id;
                    }
                    return $id;

                // For non-select fields, return the value as-is
                case 'text':
                case 'textarea':
                case 'number':
                case 'date':
                case 'datetime':
                case 'file':
                default:
                    return $id;
            }
        } catch (\Exception $e) {
            // If there's any error resolving the value, return the original value
            return $id;
        }
    }

    /**
     * Group rows data by array index for proper display
     */
    private function groupRowsDataByIndex($elements)
    {
        $rowsData = [];

        foreach ($elements as $element) {
            $elementValues = $this->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            foreach ($elementValues as $value) {
                $arrayIndex = $value->array_index ?? 0;

                if (!isset($rowsData[$arrayIndex])) {
                    $rowsData[$arrayIndex] = [];
                }

                $rowsData[$arrayIndex][$element->id] = [
                    'element' => $element,
                    'value' => $value,
                    'display_value' => $this->resolveDisplayValue($element, $value->value)
                ];
            }
        }

        return $rowsData;
    }

    /**
     * Build dependency chain for proper loading order
     */
    private function buildDependencyChain($elementsMetadata)
    {
        $chain = [];
        $levels = [];

        // Group elements by dependency level
        foreach ($elementsMetadata as $elementId => $elementData) {
            $level = $elementData['dependency_info']['dependency_level'];
            if (!isset($levels[$level])) {
                $levels[$level] = [];
            }
            $levels[$level][] = $elementId;
        }

        // Sort by level and build chain
        ksort($levels);
        foreach ($levels as $level => $elementIds) {
            $chain[] = [
                'level' => $level,
                'elements' => $elementIds,
                'is_independent' => $level === 1
            ];
        }

        return $chain;
    }

    /**
     * Get all submitted form data organized by sample_header and sample_details
     * Returns field names as keys and submitted values as values
     */
    public function getSubmittedFormData()
    {
        // Load the form with all relationships
        $this->load([
            'submissionForm.sections.elementHolders.elements' => function ($query) {
                $query->orderBy('sort_order');
            }
        ]);

        $submittedData = [
            'sample_header' => [],
            'sample_details' => []
        ];

        // Process each section
        foreach ($this->submissionForm->sections as $section) {
            foreach ($section->elementHolders as $holder) {
                foreach ($holder->elements as $element) {
                    // Get all values for this element
                    $elementValues = $this->values()
                        ->where('submission_form_element_id', $element->id)
                        ->orderBy('array_index')
                        ->get();

                    if ($elementValues->isEmpty()) {
                        continue;
                    }

                    // Determine if this is a sample_header or sample_details field
                    $isSampleHeader = false;
                    $isSampleDetails = false;

                    if ($element->is_mapped && $element->mapping_table && $element->mapping_field) {
                        if ($element->mapping_table === 'sample_headers') {
                            $isSampleHeader = true;
                        } elseif ($element->mapping_table === 'sample_details') {
                            $isSampleDetails = true;
                        }
                    }

                    // Get the field name (use mapping_field if available, otherwise use element name)
                    $fieldName = $element->mapping_field ?: $element->name;

                    // Process values based on element type and mapping
                    if ($isSampleHeader) {
                        // For sample_header fields, get the first value or all values as array
                        if ($elementValues->count() === 1) {
                            $submittedData['sample_header'][$fieldName] = $elementValues->first()->value;
                        } else {
                            $submittedData['sample_header'][$fieldName] = $elementValues->pluck('value')->toArray();
                        }
                    } elseif ($isSampleDetails) {
                        // For sample_details fields, organize by array_index
                        foreach ($elementValues as $value) {
                            $arrayIndex = $value->array_index ?? 0;
                            if (!isset($submittedData['sample_details'][$arrayIndex])) {
                                $submittedData['sample_details'][$arrayIndex] = [];
                            }
                            $submittedData['sample_details'][$arrayIndex][$fieldName] = $value->value;
                        }
                    } else {
                        // For non-mapped fields, add to sample_header by default
                        if ($elementValues->count() === 1) {
                            $submittedData['sample_header'][$fieldName] = $elementValues->first()->value;
                        } else {
                            $submittedData['sample_header'][$fieldName] = $elementValues->pluck('value')->toArray();
                        }
                    }
                }
            }
        }

        // Convert sample_details to indexed array
        $submittedData['sample_details'] = array_values($submittedData['sample_details']);

        return $submittedData;
    }

    /**
     * Get submitted form data with resolved values (IDs converted to names)
     */
    public function getSubmittedFormDataResolved()
    {
        $rawData = $this->getSubmittedFormData();
        $resolvedData = [
            'sample_header' => [],
            'sample_details' => []
        ];

        // Resolve sample_header values
        foreach ($rawData['sample_header'] as $fieldName => $value) {
            $resolvedData['sample_header'][$fieldName] = $this->resolveFieldValue($fieldName, $value);
        }

        // Resolve sample_details values
        foreach ($rawData['sample_details'] as $index => $row) {
            $resolvedData['sample_details'][$index] = [];
            foreach ($row as $fieldName => $value) {
                $resolvedData['sample_details'][$index][$fieldName] = $this->resolveFieldValue($fieldName, $value);
            }
        }

        return $resolvedData;
    }

    /**
     * Resolve field value by converting IDs to names where applicable
     */
    private function resolveFieldValue($fieldName, $value)
    {
        if (empty($value)) {
            return $value;
        }

        // Handle array values
        if (is_array($value)) {
            return array_map(function ($v) use ($fieldName) {
                return $this->resolveSingleValueByFieldName($fieldName, $v);
            }, $value);
        }

        return $this->resolveSingleValueByFieldName($fieldName, $value);
    }

    /**
     * Resolve a single value by field name
     */
    private function resolveSingleValueByFieldName($fieldName, $value)
    {
        if (empty($value)) {
            return $value;
        }

        try {
            switch ($fieldName) {
                case 'crm_customer_id':
                    $customer = DB::table('crm_customers')->where('id', $value)->first();
                    return $customer ? $customer->name : $value;

                case 'crm_unit_id':
                    $unit = DB::table('crm_company_units')->where('id', $value)->first();
                    return $unit ? $unit->name : $value;

                case 'company_sub_unit_id':
                    $subUnit = DB::table('crm_company_sub_units')->where('id', $value)->first();
                    return $subUnit ? $subUnit->name : $value;

                case 'sample_type_id':
                    $sampleType = DB::table('sample_types')->where('id', $value)->first();
                    return $sampleType ? $sampleType->name : $value;

                case 'analysis_type_id':
                    $analysisType = DB::table('analysis_types')->where('id', $value)->first();
                    return $analysisType ? $analysisType->name : $value;

                case 'sample_point_id':
                    $samplePoint = DB::table('sample_points')->where('id', $value)->first();
                    return $samplePoint ? $samplePoint->name : $value;

                case 'sample_condition_id':
                    $condition = DB::table('sample_conditions')->where('id', $value)->first();
                    return $condition ? $condition->name : $value;

                case 'main_standard':
                case 'secondary_standard':
                    $standard = DB::table('standards')->where('id', $value)->first();
                    return $standard ? $standard->name : $value;

                case 'store_id':
                    $store = DB::table('inventory_stores')->where('id', $value)->first();
                    return $store ? $store->name : $value;

                case 'store_slot_id':
                    $slot = DB::table('inventory_store_slots')->where('id', $value)->first();
                    return $slot ? $slot->name : $value;

                case 'crm_contact_id':
                    $contact = DB::table('crm_customer_contacts')->where('id', $value)->first();
                    if ($contact) {
                        $name = trim($contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name);
                        return $name ?: $value;
                    }
                    return $value;

                default:
                    return $value;
            }
        } catch (\Exception $e) {
            return $value;
        }
    }
}