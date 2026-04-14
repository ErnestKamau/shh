<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CorrectiveAction extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'corrective_actions';

    protected $fillable = [
        'capa_number',
        'non_conformance_id',
        'capa_category_id',
        'capa_category_name',
        'action_type_id',
        'action_type_name',
        'title',
        'description',
        'expected_outcome',
        'action_owner',
        'action_owner_id',
        'department',
        'due_date',
        'extended_due_date',
        'extension_reason',
        'implementation_date',
        'status_id',
        'status_name',
        'priority_id',
        'priority_name',
        'implementation_notes',
        'implementation_evidence',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'due_date' => 'date',
        'extended_due_date' => 'date',
        'implementation_date' => 'date',
    ];

    // Relationships
    public function nonConformance(): BelongsTo
    {
        return $this->belongsTo(NonConformance::class, 'non_conformance_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CapaCategory::class, 'capa_category_id');
    }

    public function actionType(): BelongsTo
    {
        return $this->belongsTo(CapaActionType::class, 'action_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(CapaStatus::class, 'status_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(CapaPriority::class, 'priority_id');
    }

    public function actionOwnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_owner_id');
    }

    public function verificationRecords(): HasMany
    {
        return $this->hasMany(VerificationRecord::class, 'corrective_action_id');
    }

    public function latestVerification()
    {
        return $this->hasOne(VerificationRecord::class, 'corrective_action_id')->latest();
    }

    public function hasVerification(): bool
    {
        return $this->latestVerification()->exists();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AuditAttachment::class, 'attachable');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(AuditNotification::class, 'notifiable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(AuditActivityLog::class, 'loggable');
    }

    public function workflowApprovals(): MorphMany
    {
        return $this->morphMany(AuditWorkflowApproval::class, 'approvable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $statusCode)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', $statusCode));
    }

    public function scopeOpen($query)
    {
        return $query->byStatus('OPEN');
    }

    public function scopeInProgress($query)
    {
        return $query->byStatus('INPROG');
    }

    public function scopeImplemented($query)
    {
        return $query->byStatus('IMPL');
    }

    public function scopeVerificationPending($query)
    {
        return $query->byStatus('VERIFY');
    }

    public function scopeClosed($query)
    {
        return $query->byStatus('CLOSED');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status_name', ['Closed', 'Verified', 'Cancelled']);
    }

    // Helper Methods
    public static function generateCapaNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "CAPA/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getEffectiveDueDateAttribute()
    {
        return $this->extended_due_date ?? $this->due_date;
    }

    public function isOverdue(): bool
    {
        if (!$this->effective_due_date) {
            return false;
        }
        
        // Only consider overdue if not closed/verified/cancelled
        if (in_array($this->status_name, ['Closed', 'Verified', 'Cancelled'])) {
            return false;
        }
        
        return $this->effective_due_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->effective_due_date->diffInDays(now());
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->effective_due_date, false);
    }

    /**
     * Get current workflow step (5-8) for this CAPA
     */
    public function getCurrentWorkflowStep(): ?int
    {
        // Step 5: Create and Assign Corrective Actions
        if (in_array($this->status_name, ['Open', 'Assigned'])) {
            return 5;
        }

        // Step 6: Implement Actions
        if (in_array($this->status_name, ['In Progress', 'Implemented'])) {
            // Check if verified
            if ($this->hasVerification()) {
                $verification = $this->latestVerification;
                if ($verification && $verification->isEffective()) {
                    return 7; // Step 7: Verified
                }
                return 7; // Step 7: Verification Pending
            }
            return 6; // Step 6: Implemented
        }

        // Step 7: QA/Auditor Verifies Effectiveness
        if ($this->status_name === 'Verification Pending') {
            return 7;
        }

        if ($this->status_name === 'Verified') {
            return 7;
        }

        // Step 8: Closed
        if ($this->status_name === 'Closed') {
            return 8;
        }

        return 5; // Default to step 5
    }

    /**
     * Check if CAPA can proceed to specified workflow step
     */
    public function canProceedToStep(int $targetStep): bool
    {
        $currentStep = $this->getCurrentWorkflowStep() ?? 5;

        // Can't go backwards (except for corrections)
        if ($targetStep < $currentStep && $targetStep !== 8) {
            return false;
        }

        // Can only proceed to next step (strict enforcement)
        if ($targetStep > $currentStep + 1 && $targetStep !== 8) {
            return false;
        }

        // Step-specific validations
        switch ($targetStep) {
            case 6: // Implement Actions
                return in_array($currentStep, [5, 6]); // Must be at step 5 or 6

            case 7: // QA/Auditor Verifies Effectiveness
                if ($currentStep < 6) {
                    return false;
                }
                // Must be implemented
                return in_array($this->status_name, ['Implemented', 'Verification Pending', 'Verified']);

            case 8: // Close Audit & NC
                if ($currentStep < 7) {
                    return false;
                }
                // Must be verified and effective
                if ($this->status_name !== 'Verified' && $this->status_name !== 'Closed') {
                    return false;
                }
                if (!$this->hasVerification()) {
                    return false;
                }
                return $this->latestVerification->isEffective();
        }

        return false;
    }

    /**
     * Get workflow step name
     */
    public function getWorkflowStepName(): ?string
    {
        $step = $this->getCurrentWorkflowStep();
        if ($step) {
            return getWorkflowStepName($step);
        }
        return null;
    }

    /**
     * Check if approval is required for current workflow step
     */
    public function requiresApproval(): bool
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return false;
        }

        return AuditWorkflowApprover::forCompany()
            ->forWorkflowStep($currentStep)
            ->required()
            ->exists();
    }

    /**
     * Get required approvers for current workflow step
     */
    public function getRequiredApprovers()
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return collect();
        }

        return AuditWorkflowApprover::forCompany()
            ->forWorkflowStep($currentStep)
            ->required()
            ->with('user')
            ->get();
    }

    /**
     * Check if all required approvers have approved for current workflow step
     */
    public function hasAllRequiredApprovals(): bool
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return true;
        }

        $requiredApprovers = AuditWorkflowApprover::forCompany()
            ->forWorkflowStep($currentStep)
            ->required()
            ->get();

        if ($requiredApprovers->isEmpty()) {
            return true;
        }

        $approvedCount = AuditWorkflowApproval::forCompany()
            ->where('approvable_type', self::class)
            ->where('approvable_id', $this->id)
            ->where('workflow_step', $currentStep)
            ->whereIn('approver_id', $requiredApprovers->pluck('user_id'))
            ->count();

        return $approvedCount >= $requiredApprovers->count();
    }

    /**
     * Check if current user can approve this workflow step
     */
    public function canUserApprove(?int $userId = null): bool
    {
        $userId = $userId ?? auth()->id();
        if (!$userId) {
            return false;
        }

        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return false;
        }

        return AuditWorkflowApprover::forCompany()
            ->forWorkflowStep($currentStep)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Get pending approvers for current workflow step
     */
    public function getPendingApprovers()
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return collect();
        }

        $requiredApprovers = AuditWorkflowApprover::forCompany()
            ->forWorkflowStep($currentStep)
            ->required()
            ->with('user')
            ->get();

        $approvedUserIds = AuditWorkflowApproval::forCompany()
            ->where('approvable_type', self::class)
            ->where('approvable_id', $this->id)
            ->where('workflow_step', $currentStep)
            ->pluck('approver_id')
            ->toArray();

        return $requiredApprovers->filter(function($approver) use ($approvedUserIds) {
            return !in_array($approver->user_id, $approvedUserIds);
        });
    }
}
