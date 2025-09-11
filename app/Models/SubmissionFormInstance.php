<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SubmissionFormInstance extends Model
{

    protected $fillable = [
        'submission_form_id',
        'form_number',
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
     * Generate a unique form number based on the form's naming convention
     */
    public function generateFormNumber(): string
    {
        $form = $this->submissionForm;
        $prefix = $form->naming_convention_prefix ?? 'SF';
        $format = $form->naming_convention_format ?? '{prefix}/{year}/{sequence}';
        $year = date('Y');
        
        // Get the highest sequence number globally for this prefix and year
        $lastInstance = self::where('form_number', 'LIKE', "{$prefix}%/{$year}/%")
            ->orderBy('form_number', 'desc')
            ->first();
            
        $sequence = 1;
        if ($lastInstance) {
            $parts = explode('/', $lastInstance->form_number);
            $lastSequence = intval(end($parts));
            $sequence = $lastSequence + 1;
        }
        
        $maxAttempts = 100;
        $attempt = 0;
        
        do {
            $attempt++;
            
            // Generate the form number
            $formNumber = str_replace(
                ['{prefix}', '{year}', '{sequence}'],
                [$prefix, $year, str_pad($sequence, 3, '0', STR_PAD_LEFT)],
                $format
            );
            
            // Check if this form number already exists (globally)
            $exists = self::where('form_number', $formNumber)->exists();
            
            if (!$exists) {
                return $formNumber;
            }
            
            // If it exists, automatically increment sequence and try again
            $sequence++;
            
        } while ($attempt < $maxAttempts);
        
        // If we still can't find a unique number, use timestamp as fallback
        $timestamp = time();
        return str_replace(
            ['{prefix}', '{year}', '{sequence}'],
            [$prefix, $year, str_pad($timestamp, 6, '0', STR_PAD_LEFT)],
            $format
        );
    }

    /**
     * Get a value by element name
     */
    public function getValueByElementName(string $elementName)
    {
        $value = $this->values()
            ->whereHas('element', function($query) use ($elementName) {
                $query->where('name', $elementName);
            })->first();
            
        return $value ? $value->value : null;
    }

    /**
     * Get all values as an associative array with element names as keys
     */
    public function getValuesArray(): array
    {
        $valuesArray = [];
        
        $this->values()->with('element')->get()->each(function($value) use (&$valuesArray) {
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
        switch($this->status) {
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
        switch($this->priority) {
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
            'submissionForm.sections.elementHolders.elements' => function($query) {
                $query->orderBy('sort_order');
            }
        ]);

        // Get all form elements
        $elements = $this->submissionForm->sections
            ->flatMap(function($section) {
                return $section->elementHolders->flatMap(function($holder) {
                    return $holder->elements;
                });
            });

        // Separate elements by mapping table
        $sampleHeaderElements = [];
        $sampleDetailElements = [];
        $sampleTypeElement = null;

        foreach ($elements as $element) {
            if ($element->is_mapped && $element->mapping_table && $element->mapping_field) {
                if ($element->mapping_table === 'sample_headers') {
                    $sampleHeaderElements[] = $element;
                    // Check if this is the sample_type field
                    if ($element->mapping_field === 'sample_type') {
                        $sampleTypeElement = $element;
                    }
                } elseif ($element->mapping_table === 'sample_details') {
                    $sampleDetailElements[] = $element;
                }
            }
        }

        // Get all values for sample_type element (from rows-section)
        $sampleTypeValues = collect();
        if ($sampleTypeElement) {
            $sampleTypeValues = $this->values()
                ->where('submission_form_element_id', $sampleTypeElement->id)
                ->orderBy('array_index')
                ->get();
        }

        // If no sample_type values found, create a single batch
        if ($sampleTypeValues->isEmpty()) {
            return [$this->createSampleBatch($sampleHeaderElements, $sampleDetailElements, null)];
        }

        // Group by unique sample_type values
        $uniqueSampleTypes = $sampleTypeValues->pluck('value')->unique();
        $sampleBatches = [];

        foreach ($uniqueSampleTypes as $sampleType) {
            $sampleBatches[] = $this->createSampleBatch(
                $sampleHeaderElements, 
                $sampleDetailElements, 
                $sampleType,
                $sampleTypeValues->where('value', $sampleType)->pluck('array_index')->toArray()
            );
        }

        return $sampleBatches;
    }

    /**
     * Create a sample batch with sample_header and sample_details
     */
    private function createSampleBatch($sampleHeaderElements, $sampleDetailElements, $sampleType = null, $arrayIndexes = [])
    {
        $sampleHeader = [];
        $sampleDetails = [];

        // Process sample header elements
        foreach ($sampleHeaderElements as $element) {
            $elementValues = $this->values()
                ->where('submission_form_element_id', $element->id)
                ->orderBy('array_index')
                ->get();

            $value = $this->getElementValue($element, $elementValues);
            
            if ($value !== null) {
                $sampleHeader[$element->mapping_field] = $value;
            }
        }

        // Override sample_type if provided
        if ($sampleType !== null) {
            $sampleHeader['sample_type'] = $sampleType;
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
                    $sampleDetails[$index][$element->mapping_field] = $value;
                }
            }
        }

        // Convert indexed array to sequential array
        $sampleDetails = array_values($sampleDetails);

        return [
            'sample_header' => $sampleHeader,
            'sample_details' => $sampleDetails
        ];
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
        $values = $elementValues->pluck('value')->filter(function($value) {
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
     * Get sample batches by sample type
     */
    public function getSampleBatchesByType(string $sampleType)
    {
        $batches = $this->getAllFieldsWithValues();
        return array_filter($batches, function($batch) use ($sampleType) {
            return ($batch['sample_header']['sample_type'] ?? null) === $sampleType;
        });
    }

    /**
     * Get all unique sample types
     */
    public function getUniqueSampleTypes()
    {
        $batches = $this->getAllFieldsWithValues();
        $sampleTypes = [];
        
        foreach ($batches as $batch) {
            $sampleType = $batch['sample_header']['sample_type'] ?? null;
            if ($sampleType && !in_array($sampleType, $sampleTypes)) {
                $sampleTypes[] = $sampleType;
            }
        }
        
        return $sampleTypes;
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
     * Get sample details for a specific sample type
     */
    public function getSampleDetailsByType(string $sampleType)
    {
        $batches = $this->getSampleBatchesByType($sampleType);
        $details = [];
        
        foreach ($batches as $batch) {
            $details = array_merge($details, $batch['sample_details']);
        }
        
        return $details;
    }
}