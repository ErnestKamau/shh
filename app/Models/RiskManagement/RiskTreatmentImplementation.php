<?php

namespace App\Models\RiskManagement;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RiskTreatmentImplementation extends Model
{
    use SoftDeletes;

    protected $table = 'risk_treatment_implementations';

    protected $fillable = [
        'risk_id',
        'treatment_plan_id',
        'assessment_id',
        'implementation_number',
        'actions_implemented',
        'responsible_user_id',
        'responsible_person',
        'start_date',
        'completion_date',
        'resources_used',
        'status',
        'observed_residual_risk',
        'effectiveness_notes',
        'approved_by_user_id',
        'approved_by',
        'approval_date',
        'next_review_date',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'completion_date' => 'date',
        'approval_date' => 'date',
        'next_review_date' => 'date',
        'observed_residual_risk' => 'integer',
    ];

    // Relationships
    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function treatmentPlan(): BelongsTo
    {
        return $this->belongsTo(RiskTreatmentPlan::class, 'treatment_plan_id');
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'assessment_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RiskAttachment::class, 'attachable');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(\App\Models\AuditModule\AuditActivityLog::class, 'loggable');
    }

    // Methods
    public static function generateImplementationNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "IMPL/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
