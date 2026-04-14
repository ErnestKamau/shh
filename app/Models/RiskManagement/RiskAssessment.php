<?php

namespace App\Models\RiskManagement;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RiskAssessment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_assessments';

    protected $fillable = [
        'risk_id',
        'assessment_number',
        'likelihood_scale_id',
        'likelihood_score',
        'severity_scale_id',
        'severity_score',
        'rpn',
        'risk_level',
        'assessment_notes',
        'assessed_by_user_id',
        'assessed_by',
        'assessment_date',
        'is_current',
        'version',
        'reassessment_reason',
        'created_by',
        'updated_by',
        'company_id',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'likelihood_score' => 'integer',
        'severity_score' => 'integer',
        'rpn' => 'integer',
        'is_current' => 'boolean',
        'version' => 'integer',
    ];

    // Relationships
    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function likelihoodScale(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AuditModule\LikelihoodScale::class, 'likelihood_scale_id');
    }

    public function severityScale(): BelongsTo
    {
        return $this->belongsTo(\App\Models\AuditModule\SeverityScale::class, 'severity_scale_id');
    }

    public function assessedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class, 'assessment_id');
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

    // Methods
    public static function generateAssessmentNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "ASSESS/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function markAsCurrent(): void
    {
        // Set all other assessments for this risk as not current
        static::where('risk_id', $this->risk_id)
            ->where('id', '!=', $this->id)
            ->update(['is_current' => false]);
        
        // Set this assessment as current
        $this->update(['is_current' => true]);
    }
}
