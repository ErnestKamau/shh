<?php

namespace App\Models\RiskManagement;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class RiskReview extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_reviews';

    protected $fillable = [
        'risk_id',
        'review_number',
        'review_date',
        'reviewed_by',
        'reviewed_by_user_id',
        'review_type',
        'review_reason',
        'review_likelihood_score',
        'review_severity_score',
        'review_rpn',
        'review_risk_level',
        'control_effectiveness_assessment',
        'controls_effective',
        'effectiveness_evidence',
        'review_findings',
        'opportunities_for_improvement',
        'new_risks_identified',
        'kpi_metrics', // NEW
        'action_required', // NEW
        'action_required_reason', // NEW
        'reassess_risk', // NEW - dedicated field for reassessment
        'review_decision',
        'decision_justification',
        'next_review_date',
        'created_by',
    ];

    protected $casts = [
        'review_date' => 'date',
        'next_review_date' => 'date',
        'review_likelihood_score' => 'integer',
        'review_severity_score' => 'integer',
        'review_rpn' => 'integer',
        'controls_effective' => 'boolean',
        'action_required' => 'boolean', // NEW
        'reassess_risk' => 'boolean', // NEW
    ];

    // Relationships
    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(RiskAttachment::class, 'attachable');
    }

    // Helper Methods
    public static function generateReviewNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "REV/{$year}/" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}


