<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditTenantForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NonConformance extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditTenantForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'non_conformances';

    protected $fillable = [
        'nc_number',
        'origin_id',
        'origin_name',
        'audit_id',
        'audit_finding_id',
        'sample_id',
        'sample_reference',
        'equipment_id',
        'equipment_reference',
        'method_id',
        'method_reference',
        'personnel_id',
        'personnel_reference',
        'sop_reference',
        'title',
        'description',
        'iso_clause_violated',
        'date_identified',
        'identified_by',
        'identified_by_user_id',
        'department',
        'department_id',
        'risk_level_id',
        'risk_level_name',
        'severity_score',
        'severity_scale_id',
        'likelihood_score',
        'likelihood_scale_id',
        'risk_assessment_notes',
        'immediate_correction',
        'immediate_correction_date',
        'immediate_correction_by',
        'status_id',
        'status_name',
        'target_closure_date',
        'actual_closure_date',
        'closed_by',
        'closure_notes',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'date_identified' => 'date',
        'immediate_correction_date' => 'date',
        'target_closure_date' => 'date',
        'actual_closure_date' => 'date',
        'severity_score' => 'integer',
        'likelihood_score' => 'integer',
    ];

    // Relationships
    public function origin(): BelongsTo
    {
        return $this->belongsTo(NcOrigin::class, 'origin_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(NcStatus::class, 'status_id');
    }

    public function riskLevel(): BelongsTo
    {
        return $this->belongsTo(RiskLevel::class, 'risk_level_id');
    }

    public function severityScale(): BelongsTo
    {
        return $this->belongsTo(SeverityScale::class, 'severity_scale_id');
    }

    public function likelihoodScale(): BelongsTo
    {
        return $this->belongsTo(LikelihoodScale::class, 'likelihood_scale_id');
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditModuleFinding::class, 'audit_finding_id');
    }

    public function rootCauseAnalysis(): HasOne
    {
        return $this->hasOne(RootCauseAnalysis::class, 'non_conformance_id');
    }

    public function rootCauseAnalyses(): HasMany
    {
        return $this->hasMany(RootCauseAnalysis::class, 'non_conformance_id');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'non_conformance_id');
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

    public function identifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identified_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Scopes
    public function scopeByStatus($query, $statusCode)
    {
        return $query->whereHas('status', fn($q) => $q->where('code', $statusCode));
    }

    public function scopeIdentified($query)
    {
        return $query->byStatus('IDENT');
    }

    public function scopeRcaInProgress($query)
    {
        return $query->byStatus('RCA');
    }

    public function scopeCapaAssigned($query)
    {
        return $query->byStatus('CAPA');
    }

    public function scopeVerificationPending($query)
    {
        return $query->byStatus('VERIFY');
    }

    public function scopeClosed($query)
    {
        return $query->byStatus('CLOSED');
    }

    // Helper Methods
    public static function generateNcNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "NC/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getRiskScoreAttribute(): int
    {
        return ($this->severity_score ?? 0) * ($this->likelihood_score ?? 0);
    }

    public function getCapaCountAttribute(): int
    {
        return $this->correctiveActions()->count();
    }

    public function getOpenCapaCountAttribute(): int
    {
        return $this->correctiveActions()->whereHas('status', fn($q) => $q->whereIn('code', ['OPEN', 'INPROG']))->count();
    }

    public function hasRca(): bool
    {
        return $this->rootCauseAnalyses()->exists();
    }

    public function canBeClosed(): bool
    {
        // Must be at workflow step 8 to close
        $currentStep = $this->getCurrentWorkflowStep();
        if ($currentStep !== 8) {
            return false;
        }

        // Check if there are any open CAPAs
        $openCapas = $this->correctiveActions()
            ->whereNotIn('status_name', ['Closed', 'Verified', 'Cancelled'])
            ->count();

        if ($openCapas > 0) {
            return false;
        }

        // All CAPAs must be verified
        foreach ($this->correctiveActions as $capa) {
            if (!in_array($capa->status_name, ['Verified', 'Closed'])) {
                return false;
            }
            if (!$capa->hasVerification() || !$capa->latestVerification->isEffective()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get current workflow step (3-8) for this NC
     */
    public function getCurrentWorkflowStep(): ?int
    {
        // Step 3: Log Non-Conformance - Identified
        if ($this->status_name === 'Identified') {
            return 3;
        }

        // Step 4: Root Cause Analysis
        if (in_array($this->status_name, ['RCA In Progress', 'RCA Complete'])) {
            $rca = $this->rootCauseAnalysis;
            if ($rca && $rca->isApproved()) {
                // Check if CAPAs exist
                if ($this->correctiveActions()->count() > 0) {
                    // Check CAPA steps
                    $maxStep = 5;
                    foreach ($this->correctiveActions as $capa) {
                        $capaStep = $capa->getCurrentWorkflowStep();
                        if ($capaStep && $capaStep > $maxStep) {
                            $maxStep = $capaStep;
                        }
                    }
                    return $maxStep;
                }
                return 5; // Step 5: Ready for CAPA assignment
            }
            return 4; // Step 4: RCA In Progress
        }

        // Step 5: Create and Assign Corrective Actions
        if ($this->status_name === 'CAPA Assigned') {
            $capas = $this->correctiveActions()->get();
            $maxStep = 5;
            foreach ($capas as $capa) {
                $capaStep = $capa->getCurrentWorkflowStep();
                if ($capaStep && $capaStep > $maxStep) {
                    $maxStep = $capaStep;
                }
            }
            return $maxStep;
        }

        // Step 8: Closed
        if ($this->status_name === 'Closed') {
            return 8;
        }

        return 3; // Default to step 3
    }

    /**
     * Check if NC can proceed to specified workflow step
     */
    public function canProceedToStep(int $targetStep): bool
    {
        $currentStep = $this->getCurrentWorkflowStep() ?? 3;

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
            case 4: // Root Cause Analysis
                return $currentStep === 3; // Must be at step 3

            case 5: // Create and Assign Corrective Actions
                if ($currentStep < 4) {
                    return false;
                }
                // Must have created RCA (not necessarily approved)
                return $this->hasRca();

            case 6: // Implement Actions
                if ($currentStep < 5) {
                    return false;
                }
                // Must have at least one CAPA
                return $this->correctiveActions()->count() > 0;

            case 7: // QA/Auditor Verifies Effectiveness
                if ($currentStep < 6) {
                    return false;
                }
                // All CAPAs must be implemented
                foreach ($this->correctiveActions as $capa) {
                    if (!in_array($capa->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed'])) {
                        return false;
                    }
                }
                return true;

            case 8: // Close Audit & NC
                if ($currentStep < 7) {
                    return false;
                }
                // All CAPAs must be verified
                foreach ($this->correctiveActions as $capa) {
                    if (!in_array($capa->status_name, ['Verified', 'Closed'])) {
                        return false;
                    }
                    if (!$capa->hasVerification() || !$capa->latestVerification->isEffective()) {
                        return false;
                    }
                }
                return true;
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

    public function isOverdue(): bool
    {
        if (!$this->target_closure_date) {
            return false;
        }
        
        // Only consider overdue if not closed
        if ($this->status_name === 'Closed' || $this->status_name === 'Cancelled') {
            return false;
        }
        
        return $this->target_closure_date->isPast();
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
