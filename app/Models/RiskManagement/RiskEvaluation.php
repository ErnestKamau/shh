<?php

namespace App\Models\RiskManagement;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RiskEvaluation extends Model
{
    use SoftDeletes;

    protected $table = 'risk_evaluations';

    protected $fillable = [
        'risk_id',
        'assessment_id',
        'evaluation_number',
        'risk_score',
        'acceptance_threshold_rpn',
        'evaluation_result',
        'evaluation_notes',
        'evaluated_by_user_id',
        'evaluated_by',
        'evaluation_date',
        'is_current',
        'escalated_to_user_id',
        'escalation_reason',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'evaluation_date' => 'date',
        'risk_score' => 'integer',
        'acceptance_threshold_rpn' => 'integer',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(RiskAssessment::class, 'assessment_id');
    }

    public function evaluatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by_user_id');
    }

    public function escalatedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escalated_to_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(\App\Models\AuditModule\AuditActivityLog::class, 'loggable');
    }

    // Scopes
    public function scopeIsCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeForRisk($query, $riskId)
    {
        return $query->where('risk_id', $riskId);
    }

    public function scopeForAssessment($query, $assessmentId)
    {
        return $query->where('assessment_id', $assessmentId);
    }

    // Methods
    public static function generateEvaluationNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "EVAL/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function markAsCurrent(): void
    {
        // Set all other evaluations for this risk as not current
        static::where('risk_id', $this->risk_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);
        
        // Set this evaluation as current
        $this->update(['is_current' => true]);
    }
}
