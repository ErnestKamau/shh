<?php

namespace App\Models;

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
        
        // Get next sequence number for this form and year
        $year = date('Y');
        $lastInstance = self::where('submission_form_id', $this->submission_form_id)
            ->where('form_number', 'LIKE', "{$prefix}%/{$year}/%")
            ->orderBy('id', 'desc')
            ->first();
            
        $sequence = 1;
        if ($lastInstance) {
            $parts = explode('/', $lastInstance->form_number);
            $sequence = intval(end($parts)) + 1;
        }
        
        return str_replace(
            ['{prefix}', '{year}', '{sequence}'],
            [$prefix, $year, str_pad($sequence, 3, '0', STR_PAD_LEFT)],
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
}