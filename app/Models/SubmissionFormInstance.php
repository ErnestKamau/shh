<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\Concerns\HasVarcharUuidRelationships;
use App\User;
use App\Models\CRM\CRMCustomer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SubmissionFormInstance extends Model implements Auditable
{
    use HasUuids;
    use HasVarcharUuidRelationships;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;


    protected $fillable = [
        'submission_form_id',
        'form_number',
        'sequence_number',
        'title',
        'submitted_by',
        'portal_account_id',
        'crm_customer_id',
        'zone_id',
        'processing_zone_id',
        'target_record_type',
        'target_record_id',
        'portal_request_id',
        'status',
        'priority',
        'due_date',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'receiving_lab_id',
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
     * Lab selected by SRO when sending the request for analyst review.
     */
    public function receivingLab(): BelongsTo
    {
        return $this->belongsTo(\App\Lab::class, 'receiving_lab_id');
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
     * Get the CRM customer who submitted this instance (populated for portal submissions).
     */
    public function crmCustomer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    /**
     * Get all batches (sample headers) linked to this form instance
     */
    public function batches(): HasMany
    {
        return $this->uuidHasMany(\App\SampleHeader::class, 'submission_form_instance_id');
    }

    /**
     * Get attachment instances linked to this template instance via portal_request_id.
     * Attachment instances store the template instance UUID in their portal_request_id column.
     */
    public function attachmentInstances(): HasMany
    {
        return $this->hasMany(static::class, 'portal_request_id', 'id');
    }

    /**
     * Workflow-specific approval/rejection forms linked to this instance.
     */
    public function workflowForms(): HasMany
    {
        return $this->hasMany(RequestWorkflowForm::class, 'submission_form_instance_id', 'id')
            ->latest('submitted_at');
    }

    /**
     * Get uploaded document attachments for this instance.
     */
    public function customAttachments(): HasMany
    {
        return $this->hasMany(SubmissionFormInstanceAttachment::class, 'submission_form_instance_id', 'id')
            ->latest();
    }

    public function analysisAcceptanceForms(): HasMany
    {
        return $this->hasMany(\App\Models\Sampleworkflow\AnalysisAcceptanceForm::class, 'submission_form_instance_id', 'id')
            ->latest();
    }

    public function workflowChecklistResponses(): HasMany
    {
        return $this->hasMany(\App\Models\Workflow\ChecklistResponse::class, 'submission_form_instance_id');
    }

    public function workflowApprovalLogs(): HasMany
    {
        return $this->hasMany(\App\Models\Workflow\ApprovalLog::class, 'submission_form_instance_id');
    }

    public function intrays(): HasMany
    {
        return $this->hasMany(SubmissionFormInstanceIntray::class, 'submission_form_instance_id')
            ->orderByDesc('created_at');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(SubmissionFormInstanceNote::class, 'submission_form_instance_id')
            ->orderByDesc('created_at');
    }

    public function latestIntray(): HasOne
    {
        return $this->hasOne(SubmissionFormInstanceIntray::class, 'submission_form_instance_id')
            ->whereIn('id', $this->latestIntrayIdSubquery());
    }

    public function activePendingIntray(): HasOne
    {
        return $this->hasOne(SubmissionFormInstanceIntray::class, 'submission_form_instance_id')
            ->where('status', SubmissionFormInstanceIntray::STATUS_PENDING)
            ->whereIn('id', $this->latestIntrayIdSubquery(SubmissionFormInstanceIntray::STATUS_PENDING));
    }

    /**
     * Latest intray row per instance (by created_at). Avoids latestOfMany(), which always
     * aggregates on the UUID primary key and fails on PostgreSQL (no MAX(uuid)).
     */
    private function latestIntrayIdSubquery(?string $status = null): \Closure
    {
        return function ($query) use ($status) {
            $query->select('id')
                ->from('submission_form_instance_intrays as intray_pick')
                ->whereColumn(
                    'intray_pick.submission_form_instance_id',
                    'submission_form_instance_intrays.submission_form_instance_id'
                )
                ->when($status !== null, fn ($q) => $q->where('intray_pick.status', $status))
                ->orderByDesc('intray_pick.created_at')
                ->orderByDesc('intray_pick.id')
                ->limit(1);
        };
    }

    /**
     * Get sample types associated with this instance's form (from the form-level pivot).
     * This is more reliable than reading from form values for portal submissions.
     */
    public function getFormSampleTypeNames(): array
    {
        if ($this->relationLoaded('submissionForm') && $this->submissionForm) {
            if ($this->submissionForm->relationLoaded('sampleTypes')) {
                return $this->submissionForm->sampleTypes->pluck('name')->filter()->values()->all();
            }
        }
        return [];
    }

    /**
     * Resolve sample type names from submitted values first, then fall back to form-level pivot mapping.
     */
    public function getResolvedSampleTypeNames(): array
    {
        $source = $this->relationLoaded('values')
            ? $this->values
            : $this->values()->with('element')->get();

        $sampleTypeIds = $source
            ->filter(function ($value) {
                $element = $value->element;
                if (!$element) {
                    return false;
                }

                if (($element->element_type ?? null) === 'sample_type_select') {
                    return true;
                }

                return ($element->mapping_field ?? null) === 'sample_type_id';
            })
            ->flatMap(function ($value) {
                return $this->extractValueTokens((string) $value->value);
            })
            ->filter()
            ->unique()
            ->values();

        if ($sampleTypeIds->isNotEmpty()) {
            $candidateIds = $sampleTypeIds
                ->map(static fn ($id) => trim((string) $id))
                ->filter(static fn ($id) => $id !== '')
                ->unique()
                ->values();

            if ($candidateIds->isEmpty()) {
                return $this->getFormSampleTypeNames();
            }

            $query = DB::table('sample_types');
            if (DB::connection()->getDriverName() === 'pgsql') {
                $query->whereIn(DB::raw('id::text'), $candidateIds->all());
            } else {
                $query->whereIn('id', $candidateIds->all());
            }

            return $query
                ->pluck('name')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return $this->getFormSampleTypeNames();
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
     * Get the Document Control Number (human-readable form number)
     * Returns the stored form_number if it's a valid document control number,
     * or generates one if needed
     */
    public function getDocumentControlNumber(): ?string
    {
        // If form_number exists and looks like a document control number (contains /), return it
        if ($this->form_number && strpos($this->form_number, '/') !== false) {
            return $this->form_number;
        }

        // If form is draft, no document control number yet
        if ($this->isDraft()) {
            return null;
        }

        // For submitted forms without a proper form_number, generate it
        if ($this->isSubmitted() && $this->submissionForm) {
            return $this->generateFormNumber();
        }

        return null;
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
     * Mark a submitted portal/LIMS template request as physically received at the lab.
     */
    public function markAsReceived($user, ?string $notes = null): bool
    {
        if ($this->status !== 'submitted') {
            return false;
        }

        $this->update([
            'status' => 'received',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'review_notes' => $notes,
        ]);

        $this->logAction('received', $user, null, $notes);

        return true;
    }

    /**
     * Request additional information from the customer (Samples Receiving — Received tab).
     */
    public function markAsInAdditionalInfo(User $user, ?string $notes = null, bool $notifyCustomer = true): bool
    {
        if ($this->status !== 'received') {
            return false;
        }

        $this->update([
            'status' => 'in_additional_info',
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
            'review_notes' => $notes,
        ]);

        $this->logAction('additional_info_requested', $user, null, $notes);

        if ($notifyCustomer) {
            $message = trim((string) $notes);
            if ($message !== '') {
                app(\App\Services\SubmissionForm\SubmissionFormAdditionalInfoService::class)
                    ->notifyCustomer($this->fresh(['submissionForm', 'crmCustomer', 'submittedBy', 'values.element']), $message, $user);
            }
        }

        return true;
    }

    /**
     * Send a submission request to analyst review (Samples Receiving).
     */
    public function markAsInReview(User $user, ?string $notes = null): bool
    {
        if (! in_array($this->status, ['submitted', 'received'], true)) {
            return false;
        }

        $previousStatus = $this->status;

        $attributes = [
            'status' => 'in_review',
            'reviewed_by' => $user->id,
            'review_notes' => $notes,
        ];

        if (! $this->reviewed_at) {
            $attributes['reviewed_at'] = now();
        }

        $this->update($attributes);

        $this->logAction('sent_for_analyst_review', $user, [
            'status' => ['from' => $previousStatus, 'to' => 'in_review'],
        ], $notes);

        foreach ($this->attachmentInstances as $attachmentInstance) {
            if ($attachmentInstance->status === 'submitted') {
                $attachmentInstance->update([
                    'status' => 'in_review',
                    'reviewed_at' => $attachmentInstance->reviewed_at ?? now(),
                ]);
            }
        }

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
            case 'submitted':
                return 'info';
            case 'received':
                return 'primary';
            case 'in_review':
                return 'warning';
            case 'in_additional_info':
                return 'warning';
            case 'complete':
                return 'success';
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
     * Submission requests visible on the Samples Request Review workflow board.
     */
    public function scopeInRequestReviewQueue($query)
    {
        return $query->whereHas('submissionForm', function ($formQuery) {
            $formQuery->where('form_type', 'template');
        })->where(function ($statusQuery) {
            $statusQuery->whereIn('status', ['in_review', 'In Review'])
                ->orWhereHas('batches', function ($batchQuery) {
                    $batchQuery->where(function ($inner) {
                        $inner->where('status', 'Samples Request Review')
                            ->orWhere('prelim_batch_status', 'Samples Request Review');
                    });
                });
        });
    }

    /**
     * Acceptance form, receipt notification, and batch exist for Request Review.
     */
    public function scopeRequestReviewAccepted($query)
    {
        return $query->whereHas('analysisAcceptanceForms', function ($acceptanceQuery) {
            $acceptanceQuery->where('status', \App\Models\Sampleworkflow\AnalysisAcceptanceForm::STATUS_COMPLETED);
        })->whereHas('batches', function ($batchQuery) {
            $batchQuery->where(function ($inner) {
                $inner->where('status', 'Samples Request Review')
                    ->orWhere('prelim_batch_status', 'Samples Request Review');
            })->whereHas('batch_attachments', function ($attachmentQuery) {
                $attachmentQuery->where('title', 'Sample Receipt Notification (GCLA 01)');
            });
        });
    }

    /**
     * In Request Review queue but not yet fully accepted (forms + batch + receipt).
     */
    public function scopeRequestReviewInReview($query)
    {
        return $query->inRequestReviewQueue()->whereNotIn(
            'submission_form_instances.id',
            static::query()->requestReviewAccepted()->select('submission_form_instances.id')
        );
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
     * Get the total number of tests (analyses) requested across all samples
     */
    public function getTestsCountAttribute(): int
    {
        $count = 0;

        // Eager load batches and their samples to avoid N+1 if not already loaded
        $batches = $this->batches()->with('samples')->get();

        if ($batches->isEmpty()) {
            return $this->getRequestedTestsCountAttribute();
        }

        foreach ($batches as $batch) {
            foreach ($batch->samples as $sample) {
                if (!empty($sample->analysis_type_id)) {
                    // Count exploded IDs, filtering out empty strings
                    $analysisIds = array_filter(explode(',', $sample->analysis_type_id));
                    $count += count($analysisIds);
                }
            }
        }

        return $count;
    }

    /**
     * Get requested tests count from submitted form values (used before a batch is created).
     */
    public function getRequestedTestsCountAttribute(): int
    {
        $source = $this->relationLoaded('values')
            ? $this->values
            : $this->values()->with('element')->get();

        $total = 0;

        foreach ($source as $value) {
            $element = $value->element;
            if (!$element) {
                continue;
            }

            $elementType = (string) ($element->element_type ?? '');
            $mappingField = (string) ($element->mapping_field ?? '');
            $elementName = Str::lower(trim((string) $element->name . ' ' . (string) $element->label));

            $isTestsElement = $elementType === 'analysis_elements_select'
                || in_array($mappingField, ['analysis_element_id', 'analyte_id'], true)
                || Str::contains($elementName, ['test required', 'tests required', 'parameter']);

            if (!$isTestsElement) {
                continue;
            }

            $tokens = $this->extractValueTokens((string) $value->value);
            $total += count($tokens);
        }

        return $total;
    }

    /**
     * Normalize stored value payloads to a token list.
     * Supports comma-separated strings and JSON arrays/objects.
     */
    private function extractValueTokens(string $rawValue): array
    {
        $rawValue = trim($rawValue);
        if ($rawValue === '') {
            return [];
        }

        $tokens = [];
        $decoded = json_decode($rawValue, true);

        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                if (is_string($item)) {
                    $tokens = array_merge($tokens, array_map('trim', explode(',', $item)));
                    continue;
                }

                if (is_array($item)) {
                    foreach (['value', 'id', 'uuid'] as $key) {
                        if (!empty($item[$key]) && is_string($item[$key])) {
                            $tokens = array_merge($tokens, array_map('trim', explode(',', $item[$key])));
                            break;
                        }
                    }
                }
            }
        } else {
            $tokens = array_map('trim', explode(',', $rawValue));
        }

        return array_values(array_filter($tokens, static fn ($token) => $token !== ''));
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
        $elementContext = Str::lower(trim((string) ($element->name ?? '') . ' ' . (string) ($element->label ?? '')));
        $tokens = $this->extractValueTokens((string) $value);

        if ($elementType === 'depended_field' && !empty($tokens)) {
            $resolved = collect($tokens)
                ->map(function ($token) use ($element) {
                    return $this->resolveDependedFieldToken($element, (string) $token);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($resolved)) {
                return implode(', ', $resolved);
            }
        }

        if ($elementType === 'zone_select' && !empty($tokens)) {
            $resolved = collect($tokens)
                ->map(fn (string $token) => $this->resolveZoneDisplayValue($token))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($resolved)) {
                return implode(', ', $resolved);
            }
        }

        if ($elementType === 'client_contact_select'
            && Str::contains($elementContext, 'customer')
            && !Str::contains($elementContext, 'contact')
            && !empty($tokens)) {
            $resolved = collect($tokens)
                ->map(fn (string $token) => $this->resolveSingleValue('client_select', $token))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($resolved)) {
                return implode(', ', $resolved);
            }
        }

        $isParameterLikeField = $elementType === 'analysis_elements_select'
            || Str::contains($elementContext, ['parameter', 'analyte', 'test required', 'tests required']);

        if ($isParameterLikeField && !empty($tokens)) {
            $resolved = collect($tokens)
                ->map(function ($token) {
                    return $this->resolveParameterToken((string) $token);
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (!empty($resolved)) {
                return implode(', ', $resolved);
            }
        }

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

                        return $name !== '' ? $name : $id;
                    }

                    // Some forms label the field "Customer" but store a CRM customer id.
                    $customer = DB::table('crm_customers')->where('id', $id)->first();

                    return $customer ? (string) $customer->name : $id;

                case 'zone_select':
                    return $this->resolveZoneDisplayValue((string) $id);

                case 'analysis_elements_select':
                    return $this->resolveParameterToken((string) $id);

                case 'depended_field':
                    return (string) $id;

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
                case 'camera_photo':
                default:
                    return $id;
            }
        } catch (\Exception $e) {
            // If there's any error resolving the value, return the original value
            return $id;
        }
    }

    /**
     * Resolve portal/public uploaded file paths into a reachable URL.
     */
    public function resolveUploadedMediaUrl(?string $candidate): ?string
    {
        $candidate = trim((string) $candidate);
        if ($candidate === '' || $candidate === 'N/A') {
            return null;
        }

        if (str_starts_with($candidate, 'http://') || str_starts_with($candidate, 'https://')) {
            $parsedPath = (string) (parse_url($candidate, PHP_URL_PATH) ?: '');
            if ($parsedPath !== '') {
                $normalizedFromUrl = $this->normalizeUploadedMediaPath($parsedPath);
                if (str_starts_with($normalizedFromUrl, 'portal/')) {
                    return route('portal-media.proxy', ['path' => $normalizedFromUrl]);
                }
            }

            return $candidate;
        }

        $normalized = $this->normalizeUploadedMediaPath($candidate);

        $portalApiBase = rtrim((string) config('services.portal_relay.auth_api_base_url'), '/');

        if (str_starts_with($normalized, 'portal/')) {
            return route('portal-media.proxy', ['path' => $normalized]);
        }

        $localUrl = asset('storage/' . $normalized);
        $localPath = public_path('storage/' . $normalized);

        if (is_file($localPath)) {
            return $localUrl;
        }

        if ($portalApiBase !== '') {
            return $portalApiBase . '/storage/' . $normalized;
        }

        return $localUrl;
    }

    private function normalizeUploadedMediaPath(string $path): string
    {
        $normalized = ltrim(trim($path), '/');

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return ltrim($normalized, '/');
    }

    private function resolveParameterToken(string $id): string
    {
        $id = trim($id);
        if ($id === '') {
            return $id;
        }

        $analyte = DB::table('analytes')->where('id', $id)->first();
        if ($analyte && !empty($analyte->name)) {
            return (string) $analyte->name;
        }

        $analysisElement = DB::table('analysis_elements')
            ->leftJoin('analytes', 'analytes.id', '=', 'analysis_elements.analyte_id')
            ->where('analysis_elements.id', $id)
            ->select('analysis_elements.method as analysis_element_name', 'analytes.name as analyte_name')
            ->first();

        if ($analysisElement) {
            return (string) ($analysisElement->analyte_name ?: $analysisElement->analysis_element_name ?: $id);
        }

        return $id;
    }

    private function resolveZoneDisplayValue(string $id): string
    {
        $id = trim($id);
        if ($id === '') {
            return $id;
        }

        $zone = \App\Zone::query()->find($id);

        if (!$zone) {
            return $id;
        }

        return $zone->key
            ? ($zone->key . ' — ' . $zone->value)
            : (string) $zone->value;
    }

    private function resolveDependedFieldToken($element, string $id): string
    {
        $id = trim($id);
        if ($id === '') {
            return $id;
        }

        $sourceTable = (string) ($element->source_table ?? '');
        $sourceField = (string) ($element->source_field ?? '');

        if ($sourceTable === '' || $sourceField === '') {
            return $id;
        }

        if (!DB::getSchemaBuilder()->hasTable($sourceTable)
            || !DB::getSchemaBuilder()->hasColumn($sourceTable, 'id')
            || !DB::getSchemaBuilder()->hasColumn($sourceTable, $sourceField)) {
            return $id;
        }

        $query = DB::table($sourceTable);
        if (DB::connection()->getDriverName() === 'pgsql') {
            $query->whereRaw('id::text = ?', [$id]);
        } else {
            $query->where('id', $id);
        }

        $record = $query->first([$sourceField]);

        return $record ? (string) data_get($record, $sourceField, $id) : $id;
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