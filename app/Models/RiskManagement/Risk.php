<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use App\Models\AuditModule\Audit;
use App\Models\AuditModule\AuditModuleFinding;
use App\Models\AuditModule\NonConformance;
use App\Models\AuditModule\CorrectiveAction;
use App\Models\AuditModule\LikelihoodScale;
use App\Models\AuditModule\SeverityScale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Risk extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risks';

    protected $fillable = [
        'risk_number',
        'title',
        'description',
        'initial_comments', // NEW
        'category_id',
        'category_name',
        'other_source_id',
        'other_source_name',
        'sample_id',
        'sample_reference',
        'method_id',
        'method_reference',
        'risk_owner_name',
        'risk_owner_id',
        'department',
        'department_id',
        'date_identified',
        'identified_by',
        'identified_by_user_id',
        'audit_id',
        'audit_finding_id',
        'non_conformance_id',
        'complaint_id',
        'equipment_id',
        'personnel_id',
        'related_entities',
        'likelihood_scale_id',
        'likelihood_score',
        'severity_scale_id',
        'severity_score',
        'rpn',
        'risk_level',
        'assessment_notes',
        'assessment_date',
        'evaluation_result',
        'evaluation_notes',
        'evaluation_date',
        'acceptance_threshold_rpn',
        'acceptance_criteria',
        'status_id',
        'status_name',
        'workflow_step',
        'current_assessment_id', // NEW
        'current_evaluation_id', // NEW
        'requires_treatment',
        'treatment_justification',
        'review_frequency_days',
        'next_review_date',
        'last_review_date',
        'residual_likelihood_score',
        'residual_severity_score',
        'residual_rpn',
        'residual_risk_level',
        'closure_type',
        'closure_justification',
        'lessons_learned', // NEW
        'closure_date',
        'closed_by',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'date_identified' => 'date',
        'assessment_date' => 'date',
        'evaluation_date' => 'date',
        'next_review_date' => 'date',
        'last_review_date' => 'date',
        'closure_date' => 'date',
        'likelihood_score' => 'integer',
        'severity_score' => 'integer',
        'rpn' => 'integer',
        'residual_likelihood_score' => 'integer',
        'residual_severity_score' => 'integer',
        'residual_rpn' => 'integer',
        'workflow_step' => 'integer',
        'review_frequency_days' => 'integer',
        'acceptance_threshold_rpn' => 'integer',
        'requires_treatment' => 'boolean',
        'related_entities' => 'array',
    ];

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(RiskCategory::class, 'category_id');
    }

    public function otherSource(): BelongsTo
    {
        return $this->belongsTo(RiskSource::class, 'other_source_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(RiskSource::class, 'other_source_id');
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(\App\SampleHeader::class, 'sample_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(\App\AnalysisMethod::class, 'method_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Equipments\Equipment::class, 'equipment_id');
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'personnel_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(RiskStatus::class, 'status_id');
    }

    public function likelihoodScale(): BelongsTo
    {
        return $this->belongsTo(LikelihoodScale::class, 'likelihood_scale_id');
    }

    public function severityScale(): BelongsTo
    {
        return $this->belongsTo(SeverityScale::class, 'severity_scale_id');
    }

    public function riskOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'risk_owner_id');
    }

    public function identifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'identified_by_user_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function audit(): BelongsTo
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function auditFinding(): BelongsTo
    {
        return $this->belongsTo(AuditModuleFinding::class, 'audit_finding_id');
    }

    public function nonConformance(): BelongsTo
    {
        return $this->belongsTo(NonConformance::class, 'non_conformance_id');
    }

    public function treatmentPlans(): HasMany
    {
        return $this->hasMany(RiskTreatmentPlan::class, 'risk_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(RiskReview::class, 'risk_id')->orderBy('review_date', 'desc');
    }

    public function latestReview(): HasMany
    {
        return $this->hasMany(RiskReview::class, 'risk_id')->orderBy('review_date', 'desc')->limit(1);
    }

    // NEW: Assessment relationships
    public function assessments(): HasMany
    {
        return $this->hasMany(RiskAssessment::class, 'risk_id')->orderBy('version', 'desc');
    }

    public function currentAssessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'current_assessment_id');
    }

    // NEW: Evaluation relationships
    public function evaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class, 'risk_id')->orderBy('created_at', 'desc');
    }

    public function currentEvaluation(): BelongsTo
    {
        return $this->belongsTo(RiskEvaluation::class, 'current_evaluation_id');
    }

    // NEW: Treatment Implementation relationships
    public function treatmentImplementations(): HasMany
    {
        return $this->hasMany(RiskTreatmentImplementation::class, 'risk_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RiskAttachment::class, 'attachable');
    }

    public function processLinks(): HasMany
    {
        return $this->hasMany(RiskProcessLink::class, 'risk_id')->with('businessProcess', 'creator');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(RiskNotification::class, 'notifiable');
    }

    public function workflowApprovals(): MorphMany
    {
        return $this->morphMany(\App\Models\AuditModule\AuditWorkflowApproval::class, 'approvable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(\App\Models\AuditModule\AuditActivityLog::class, 'loggable');
    }

    // Scopes
    public function scopeForCompany($query)
    {
        $companyId = getUserCompany();
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $statusCode)
    {
        return $query->whereHas('status', function ($q) use ($statusCode) {
            $q->where('code', $statusCode);
        });
    }

    public function scopeByWorkflowStep($query, $step)
    {
        return $query->where('workflow_step', $step);
    }

    public function scopeOpen($query)
    {
        return $query->where('workflow_step', '<', 8); // Step 8 is Closed
    }

    public function scopeClosed($query)
    {
        return $query->where('workflow_step', 8); // Step 8 is Closed
    }

    public function scopeRequiringReview($query)
    {
        return $query->whereNotNull('next_review_date')
            ->where('next_review_date', '<=', now())
            ->where('workflow_step', '<', 8); // Step 8 is Closed
    }

    // Helper Methods
    public static function generateRiskNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "RISK/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate RPN from likelihood and severity scores using configurable scoring method
     */
    public function calculateRPN(): ?int
    {
        if (!$this->likelihood_score || !$this->severity_score) {
            return null;
        }

        // Get scoring configuration
        $scoringConfig = \App\Models\RiskManagement\RiskScoringConfig::getDefault(getUserCompany());
        
        if ($scoringConfig) {
            return $scoringConfig->calculateRPN($this->likelihood_score, $this->severity_score);
        }

        // Default: multiplicative method
        return $this->likelihood_score * $this->severity_score;
    }

    /**
     * Determine risk level from RPN using configurable thresholds
     */
    public function determineRiskLevel(?int $rpn = null): ?string
    {
        $rpn = $rpn ?? $this->rpn;
        if (!$rpn) {
            return null;
        }

        $companyId = getUserCompany();
        
        // Try to get configurable risk level thresholds if table exists
        try {
            $thresholds = \App\Models\RiskManagement\RiskLevelThreshold::forCompany($companyId)
                ->active()
                ->orderBy('order_index')
                ->get();

            if ($thresholds->isNotEmpty()) {
                foreach ($thresholds as $threshold) {
                    if ($threshold->matchesRPN($rpn)) {
                        return $threshold->risk_level;
                    }
                }
            }
        } catch (\Exception $e) {
            // Table doesn't exist or query failed, use fallback logic
            \Log::debug('RiskLevelThreshold table not available, using default logic: ' . $e->getMessage());
        }

        // Fallback to default logic if no thresholds configured or table doesn't exist
        if ($rpn >= 16 && $rpn <= 25) {
            return 'Critical';
        } elseif ($rpn >= 10 && $rpn <= 15) {
            return 'High';
        } elseif ($rpn >= 5 && $rpn <= 9) {
            return 'Medium';
        } elseif ($rpn >= 1 && $rpn <= 4) {
            return 'Low';
        }

        return null;
    }

    /**
     * Get review frequency for this risk based on risk level
     */
    public function getReviewFrequency(): ?int
    {
        if (!$this->risk_level) {
            return null;
        }

        return \App\Models\RiskManagement\RiskReviewFrequency::getFrequencyForLevel(
            $this->risk_level,
            getUserCompany()
        );
    }

    /**
     * Get acceptance criteria applicable to this risk
     */
    public function getAcceptanceCriteria(): ?\App\Models\RiskManagement\RiskAcceptanceCriteria
    {
        return \App\Models\RiskManagement\RiskAcceptanceCriteria::getApplicableCriteria(
            $this->category_id,
            getUserCompany()
        );
    }

    /**
     * Check if risk requires treatment based on acceptance criteria
     */
    public function requiresTreatment(): bool
    {
        $criteria = $this->getAcceptanceCriteria();
        if (!$criteria) {
            // Default: require treatment if RPN > 15
            return $this->rpn > 15;
        }

        if ($criteria->can_skip_treatment && $criteria->isAcceptable($this->rpn ?? 0)) {
            return false;
        }

        return !$criteria->isAcceptable($this->rpn ?? 0);
    }

    /**
     * Get current workflow step from status configuration
     */
    public function getCurrentWorkflowStep(): ?int
    {
        if ($this->workflow_step) {
            return $this->workflow_step;
        }

        if (!$this->status_name) {
            return 2; // Default to step 2 (Identified)
        }

        $status = RiskStatus::where('name', $this->status_name)
            ->where(function ($q) {
                $companyId = getUserCompany();
                if ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                    return;
                }

                $q->whereNull('company_id');
            })
            ->first();

        if ($status && $status->workflow_step !== null) {
            return $status->workflow_step;
        }

        return 2; // Default to step 2 (Identified)
    }

    /**
     * Get the next available workflow status based on workflow step mapping
     */
    public function getNextWorkflowStatus(): ?RiskStatus
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return getActiveRiskStatuses()->where('workflow_step', 2)->first(); // Step 2 is Identified
        }

        $nextStep = $currentStep + 1;
        if ($nextStep > 8) {
            return null; // Already at the last step (Step 8 is Closed)
        }

        $companyId = getUserCompany();
        return RiskStatus::active()
            ->where('workflow_step', $nextStep)
            ->where(function ($q) use ($companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                    return;
                }

                $q->whereNull('company_id');
            })
            ->ordered()
            ->first();
    }

    /**
     * Check if risk can proceed to next workflow status
     */
    public function canProceedToNextStatus(): bool
    {
        $nextStatus = $this->getNextWorkflowStatus();
        if (!$nextStatus) {
            return false;
        }

        $currentStep = $this->getCurrentWorkflowStep() ?? 1;
        $nextStep = $nextStatus->workflow_step ?? ($currentStep + 1);

        return $this->canProceedToStep($nextStep);
    }

    /**
     * Check if risk can proceed to specified workflow step
     */
    public function canProceedToStep(int $targetStep): bool
    {
        $currentStep = $this->getCurrentWorkflowStep() ?? 1;

        // Can't go backwards (except for corrections)
        if ($targetStep < $currentStep && $targetStep !== 7) {
            return false;
        }

        // Can only proceed to next step (strict enforcement)
        if ($targetStep > $currentStep + 1 && $targetStep !== 7) {
            return false;
        }

        // Step-specific validations
        switch ($targetStep) {
            case 2: // Identified (from creation)
                return $currentStep === 1 && !empty($this->title);

            case 3: // Under Assessment
                // Can proceed from step 2 (Identified) to step 3 (Under Assessment) without assessment
                // Assessment is done IN step 3, not before entering it
                if ($currentStep < 2) {
                    return false;
                }
                // No requirement for assessment scores - assessment happens in this step
                return true;

            case 4: // Under Evaluation
                if ($currentStep < 3) {
                    return false;
                }
                // Must have completed assessment (likelihood and severity scores) before evaluation
                return !empty($this->likelihood_score) && !empty($this->severity_score) && !empty($this->rpn);

            case 5: // Treatment Planning
                if ($currentStep < 4) {
                    return false;
                }
                // Unacceptable risks must have treatment plan
                if ($this->evaluation_result === 'Unacceptable') {
                    return $this->treatmentPlans()->count() > 0;
                }
                return true;

            case 6: // Treatment Implementation
                if ($currentStep < 5) {
                    return false;
                }
                // Must have at least one treatment plan
                if ($this->treatmentPlans()->count() === 0) {
                    return false;
                }
                return true;

            case 7: // Risk Monitoring
                if ($currentStep < 6) {
                    return false;
                }
                // If treatment was required, all treatment plans must be implemented
                if ($this->requires_treatment) {
                    $allImplemented = $this->treatmentPlans()
                        ->where('implementation_status', 'Completed')
                        ->count() === $this->treatmentPlans()->count();
                    if (!$allImplemented) {
                        return false;
                    }
                }
                return true;

            case 8: // Risk Closed
                if ($currentStep < 7) {
                    return false;
                }
                // Must have at least one review
                if ($this->reviews()->count() === 0) {
                    return false;
                }
                // If treatment plans exist, they must be completed
                if ($this->treatmentPlans()->count() > 0) {
                    $allCompleted = $this->treatmentPlans()
                        ->whereIn('implementation_status', ['Completed', 'Cancelled'])
                        ->count() === $this->treatmentPlans()->count();
                    if (!$allCompleted) {
                        return false;
                    }
                }
                // Residual risk must be acceptable or justified
                if ($this->residual_rpn && $this->residual_rpn > ($this->acceptance_threshold_rpn ?? 15)) {
                    return !empty($this->closure_justification);
                }
                return true;
        }

        return false;
    }

    /**
     * Check if risk requires review (based on next_review_date)
     */
    public function requiresReview(): bool
    {
        if (!$this->next_review_date) {
            return false;
        }

        if ($this->workflow_step >= 8) {
            return false; // Closed risks don't need review (Step 8 is Closed)
        }

        return now()->greaterThanOrEqualTo($this->next_review_date);
    }

    /**
     * Check if risk can be closed
     */
    public function canBeClosed(): bool
    {
        return $this->canProceedToStep(7);
    }

    /**
     * Get status by code
     */
    public static function getStatusByCode(string $code): ?RiskStatus
    {
        $companyId = getUserCompany();
        return RiskStatus::active()
            ->where('code', $code)
            ->where(function ($q) use ($companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                    return;
                }

                $q->whereNull('company_id');
            })
            ->first();
    }

    /**
     * Get status by name
     */
    public static function getStatusByName(string $name): ?RiskStatus
    {
        $companyId = getUserCompany();
        return RiskStatus::active()
            ->where('name', $name)
            ->where(function ($q) use ($companyId) {
                if ($companyId) {
                    $q->where('company_id', $companyId)->orWhereNull('company_id');
                    return;
                }

                $q->whereNull('company_id');
            })
            ->first();
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

        return \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
            ->forModule('risk')
            ->forWorkflowStep($currentStep)
            ->required()
            ->with('user')
            ->get();
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

        $requiredApprovers = \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
            ->forModule('risk')
            ->forWorkflowStep($currentStep)
            ->required()
            ->with('user')
            ->get();

        $approvedUserIds = \App\Models\AuditModule\AuditWorkflowApproval::forCompany()
            ->where('approvable_type', self::class)
            ->where('approvable_id', $this->id)
            ->where('workflow_step', $currentStep)
            ->pluck('approver_id')
            ->toArray();

        return $requiredApprovers->filter(function($approver) use ($approvedUserIds) {
            return !in_array($approver->user_id, $approvedUserIds);
        });
    }

    /**
     * Check if all required approvers have approved for current workflow step
     */
    public function hasAllRequiredApprovals(): bool
    {
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return true; // No step means no approval needed
        }

        $requiredApprovers = \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
            ->forModule('risk')
            ->forWorkflowStep($currentStep)
            ->required()
            ->get();

        if ($requiredApprovers->isEmpty()) {
            return true; // No required approvers means approval not needed
        }

        $approvedCount = \App\Models\AuditModule\AuditWorkflowApproval::forCompany()
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

        return \App\Models\AuditModule\AuditWorkflowApprover::forCompany()
            ->forModule('risk')
            ->forWorkflowStep($currentStep)
            ->where('user_id', $userId)
            ->exists();
    }
}

