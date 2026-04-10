<?php

namespace App\Models\RiskManagement;

use App\Models\AuditModule\CorrectiveAction;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RiskTreatmentPlan extends Model
{
    use SoftDeletes;

    protected $table = 'risk_treatment_plans';

    protected $fillable = [
        'risk_id',
        'treatment_type_id',
        'treatment_type_name',
        'priority', // NEW
        'title',
        'description',
        'control_measures',
        'expected_outcome',
        'resources_required', // NEW
        'residual_risk_expected', // NEW
        'responsible_person',
        'responsible_user_id',
        'department',
        'target_completion_date',
        'actual_completion_date',
        'extension_reason',
        'implementation_status',
        'implementation_notes',
        'implementation_start_date',
        'implementation_end_date',
        'approved_by_user_id', // NEW
        'approved_by', // NEW
        'approval_date', // NEW
        'approval_notes', // NEW
        'capa_id',
        'related_actions',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'target_completion_date' => 'date',
        'actual_completion_date' => 'date',
        'implementation_start_date' => 'date',
        'implementation_end_date' => 'date',
        'related_actions' => 'array',
    ];

    // Relationships
    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function treatmentType(): BelongsTo
    {
        return $this->belongsTo(TreatmentType::class, 'treatment_type_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function capa(): BelongsTo
    {
        return $this->belongsTo(CorrectiveAction::class, 'capa_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RiskAttachment::class, 'attachable');
    }

    // NEW: Treatment Implementation relationships
    public function implementations(): HasMany
    {
        return $this->hasMany(RiskTreatmentImplementation::class, 'treatment_plan_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(\App\Models\AuditModule\AuditActivityLog::class, 'loggable');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('implementation_status', 'Completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('implementation_status', ['Planned', 'In Progress']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_completion_date', '<', now())
            ->whereNotIn('implementation_status', ['Completed', 'Cancelled']);
    }
}


