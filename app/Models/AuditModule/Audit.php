<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Audit extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'iso_audits';

    protected $fillable = [
        'audit_number',
        'revision_number',
        'audit_type_id',
        'audit_type_name',
        'title',
        'objective',
        'scope',
        'criteria',
        'department',
        'checklist_id',
        'lead_auditor_name',
        'lead_auditor_id',
        'audit_team',
        'auditee_name',
        'auditee_department_id',
        'auditee_department_name',
        'scheduled_date',
        'start_date',
        'end_date',
        'report_date',
        'closure_date',
        'status_id',
        'status_name',
        'executive_summary',
        'conclusions',
        'recommendations',
        'created_by',
        'updated_by',
        'closed_by',
        'company_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'report_date' => 'date',
        'closure_date' => 'date',
        'audit_team' => 'array',
    ];

    // Relationships
    public function auditType(): BelongsTo
    {
        return $this->belongsTo(AuditType::class, 'audit_type_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AuditStatus::class, 'status_id');
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(AuditChecklist::class, 'checklist_id');
    }

    public function checklists(): BelongsToMany
    {
        return $this->belongsToMany(AuditChecklist::class, 'audit_checklist_audit', 'audit_id', 'audit_checklist_id')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderByPivot('order_index');
    }

    public function checklistItemResponses(): HasMany
    {
        return $this->hasMany(AuditChecklistItemResponse::class, 'audit_id');
    }

    public function leadAuditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lead_auditor_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(AuditTeamMember::class, 'audit_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(AuditModuleFinding::class, 'audit_id')->orderBy('order_index');
    }

    public function nonConformances(): HasMany
    {
        return $this->hasMany(NonConformance::class, 'audit_id');
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

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Scopes
    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId);
    }

    public function scopeByStatus($query, $statusCode)
    {
        return $query->whereHas('status', function ($q) use ($statusCode) {
            $q->where('code', $statusCode);
        });
    }

    public function scopeScheduled($query)
    {
        return $query->byStatus('SCHED');
    }

    public function scopeInProgress($query)
    {
        return $query->byStatus('INPROG');
    }

    public function scopeClosed($query)
    {
        return $query->byStatus('CLOSED');
    }

    // Helper Methods
    public static function generateAuditNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "AUD/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function getFindingsCountAttribute(): int
    {
        return $this->findings()->count();
    }

    public function getOpenFindingsCountAttribute(): int
    {
        return $this->findings()->whereHas('status', function ($q) {
            $q->where('code', 'OPEN');
        })->count();
    }

    public function getNonConformancesCountAttribute(): int
    {
        return $this->nonConformances()->count();
    }

    public function canBeClosed(): bool
    {
        // Must be at workflow step 8 to close
        return $this->getCurrentWorkflowStep() === 8 && $this->canProceedToStep(8);
    }

    /**
     * Get current workflow step from status configuration
     * Uses workflow_step from audit_statuses table (dynamic configuration)
     */
    public function getCurrentWorkflowStep(): ?int
    {
        if (!$this->status_name) {
            return null;
        }

        // Get status from database with workflow_step
        $status = AuditStatus::where('name', $this->status_name)
            ->where(function($q) {
                $companyId = getUserCompany() ?? 0;
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->first();

        if ($status && $status->workflow_step !== null) {
            return $status->workflow_step;
        }

        // Fallback: Check if status_name matches workflow step names (for backward compatibility)
        $workflowSteps = getAuditWorkflowSteps();
        $stepNum = array_search($this->status_name, $workflowSteps);
        if ($stepNum !== false) {
            return $stepNum;
        }

        return null;
    }
    
    /**
     * Get workflow step name from current status (for display)
     */
    public function getWorkflowStepName(): ?string
    {
        return $this->status_name;
    }
    
    /**
     * Get status by code/keyname for dynamic workflow management
     */
    public static function getStatusByCode(string $code): ?AuditStatus
    {
        $companyId = getUserCompany() ?? 0;
        return AuditStatus::active()
            ->where('code', $code)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->first();
    }
    
    /**
     * Get status by name for dynamic workflow management
     */
    public static function getStatusByName(string $name): ?AuditStatus
    {
        $companyId = getUserCompany() ?? 0;
        return AuditStatus::active()
            ->where('name', $name)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->first();
    }
    
    /**
     * Get the next available workflow status based on workflow step mapping
     */
    public function getNextWorkflowStatus(): ?AuditStatus
    {
        if (!$this->status_name) {
            // If no status, get the first status
            return getActiveAuditStatuses()->first();
        }

        // Get current workflow step
        $currentStep = $this->getCurrentWorkflowStep();
        if (!$currentStep) {
            return null;
        }

        // Get next step
        $nextStep = $currentStep + 1;
        if ($nextStep > 7) {
            return null; // Already at the last step
        }

        // Find statuses with the next workflow_step from database configuration
        $companyId = getUserCompany() ?? 0;
        $nextStatus = AuditStatus::active()
            ->where('workflow_step', $nextStep)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->ordered()
            ->first();
        
        if ($nextStatus) {
            return $nextStatus;
        }

        // Fallback: If no status found with workflow_step, try to find by order_index
        $currentStatus = AuditStatus::where('name', $this->status_name)
            ->where(function($q) {
                $companyId = getUserCompany() ?? 0;
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->first();

        if (!$currentStatus) {
            return null;
        }

        // Get next status by order_index as fallback
        return AuditStatus::active()
            ->where('order_index', '>', $currentStatus->order_index)
            ->where(function($q) use ($companyId) {
                $q->where('company_id', $companyId)->orWhere('company_id', 0);
            })
            ->ordered()
            ->first();
    }
    
    /**
     * Check if audit can proceed to next workflow status
     */
    public function canProceedToNextStatus(): bool
    {
        $nextStatus = $this->getNextWorkflowStatus();
        if (!$nextStatus) {
            return false;
        }

        $currentStep = $this->getCurrentWorkflowStep() ?? 1;
        
        // Get next step from database workflow_step configuration
        $nextStep = $nextStatus->workflow_step;
        
        // Fallback to order_index if workflow_step is not set (backward compatibility)
        if ($nextStep === null) {
            $nextStep = $nextStatus->order_index;
        }

        // Additional check: if at step 2 (Record Findings & NC), ensure all findings requiring NCs have NCs raised
        if ($currentStep === 2 && !$this->allRequiredNCsRaised()) {
            return false;
        }

        // Additional check: if at step 3 (Root Cause Analysis), ensure all NCs have root cause analyses
        if ($currentStep === 3 && !$this->allNCsHaveRootCauseAnalyses()) {
            return false;
        }

        return $this->canProceedToStep($nextStep);
    }

    /**
     * Get findings that require NC but don't have one raised yet
     */
    public function getFindingsRequiringNC(): \Illuminate\Support\Collection
    {
        return $this->findings()
            ->whereHas('findingCategory', function($q) {
                $q->where('requires_capa', true);
            })
            ->whereDoesntHave('nonConformance')
            ->get();
    }

    /**
     * Check if all findings requiring NCs have NCs raised
     */
    public function allRequiredNCsRaised(): bool
    {
        return $this->getFindingsRequiringNC()->isEmpty();
    }

    /**
     * Get NCs that do not have root cause analyses
     */
    public function getNCsWithoutRootCauseAnalysis(): \Illuminate\Support\Collection
    {
        return $this->nonConformances()
            ->whereDoesntHave('rootCauseAnalysis')
            ->get();
    }

    /**
     * Check if all NCs have root cause analyses
     */
    public function allNCsHaveRootCauseAnalyses(): bool
    {
        $ncs = $this->nonConformances()->with('rootCauseAnalysis')->get();
        if ($ncs->count() === 0) {
            return true; // No NCs means requirement is satisfied
        }
        
        foreach ($ncs as $nc) {
            if (!$nc->hasRca()) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Check if audit can proceed to specified workflow step
     */
    public function canProceedToStep(int $targetStep): bool
    {
        $currentStep = $this->getCurrentWorkflowStep() ?? 1;

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
            case 2: // Perform Audit & Record Findings
                return $currentStep === 1; // Must be at step 1

            case 3: // Log Non-Conformance (Record Findings & NC)
                if ($currentStep < 2) {
                    return false;
                }
                // Cannot proceed from step 2 without findings
                if ($currentStep === 2 && $this->findings()->count() === 0) {
                    return false;
                }
                // If at step 3, check if all findings requiring NCs have NCs raised
                if ($currentStep === 3) {
                    return $this->allRequiredNCsRaised();
                }
                // Must have findings to create NC
                return $this->findings()->count() > 0;

            case 4: // Root Cause Analysis
                // Cannot proceed from step 2 without findings
                if ($currentStep === 2 && $this->findings()->count() === 0) {
                    return false;
                }
                if ($currentStep < 3) {
                    return false;
                }
                // Must have at least one NC
                if ($this->nonConformances()->count() === 0) {
                    return false;
                }
                // If currently at step 4, check that all NCs have root cause analyses before proceeding
                if ($currentStep === 4) {
                    return $this->allNCsHaveRootCauseAnalyses();
                }
                return true;

            case 5: // Create and Assign Corrective Actions
                // Cannot proceed from step 2 without findings
                if ($currentStep === 2 && $this->findings()->count() === 0) {
                    return false;
                }
                if ($currentStep < 4) {
                    return false;
                }
                // All NCs must have created Root Cause Analysis (not necessarily approved)
                $ncs = $this->nonConformances()->with('rootCauseAnalysis')->get();
                if ($ncs->count() === 0) {
                    return false;
                }
                foreach ($ncs as $nc) {
                    if (!$nc->hasRca()) {
                        return false;
                    }
                }
                return true;

            case 6: // Implement Actions
                // Cannot proceed from step 2 without findings
                if ($currentStep === 2 && $this->findings()->count() === 0) {
                    return false;
                }
                if ($currentStep < 5) {
                    return false;
                }
                // Must have at least one CAPA
                $hasCapa = false;
                foreach ($this->nonConformances as $nc) {
                    if ($nc->correctiveActions()->count() > 0) {
                        $hasCapa = true;
                        break;
                    }
                }
                return $hasCapa;

            case 7: // QA/Auditor Verifies Effectiveness
                // Cannot proceed from step 2 without findings
                if ($currentStep === 2 && $this->findings()->count() === 0) {
                    return false;
                }
                if ($currentStep < 6) {
                    return false;
                }
                // All CAPAs must be implemented
                foreach ($this->nonConformances as $nc) {
                    foreach ($nc->correctiveActions as $capa) {
                        if (!in_array($capa->status_name, ['Implemented', 'Verification Pending', 'Verified', 'Closed'])) {
                            return false;
                        }
                    }
                }
                return true;

            case 8: // Close Audit & NC
                if ($currentStep < 7) {
                    return false;
                }
                // Only requirement: All corrective actions (if any) must have a verification record
                // The workflow has changed - verification record existence is what matters, not the result
                // Eager load verification records to avoid N+1 queries
                $this->load(['nonConformances.correctiveActions.latestVerification']);
                
                foreach ($this->nonConformances as $nc) {
                    foreach ($nc->correctiveActions as $capa) {
                        // Only check if verification record exists - bypass result name check
                        if (!$capa->hasVerification()) {
                            return false;
                        }
                    }
                }
                // If no CAPAs exist, audit can still be closed (no requirement to have CAPAs)
        return true;
        }

        return false;
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
            ->forModule('audit')
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
            return true; // No step means no approval needed
        }

        $requiredApprovers = AuditWorkflowApprover::forCompany()
            ->forModule('audit')
            ->forWorkflowStep($currentStep)
            ->required()
            ->get();

        if ($requiredApprovers->isEmpty()) {
            return true; // No required approvers means approval not needed
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
            ->forModule('audit')
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
            ->forModule('audit')
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
