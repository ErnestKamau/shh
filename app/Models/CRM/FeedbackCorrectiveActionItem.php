<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class FeedbackCorrectiveActionItem extends Model
{
    protected $table = 'crm_feedback_corrective_action_items';

    protected $fillable = [
        'feedback_corrective_action_id',
        'source_type',
        'evaluation_metric_id',
        'issue_summary',
        'root_cause',
        'corrective_action_plan',
        'responsible_user_id',
        'target_date',
        'completion_date',
        'effectiveness_notes',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completion_date' => 'date',
    ];

    const SOURCE_LOW_RATING = 'low_rating';
    const SOURCE_REPORTED_ISSUE = 'reported_issue';

    public function correctiveAction()
    {
        return $this->belongsTo(FeedbackCorrectiveAction::class, 'feedback_corrective_action_id');
    }

    public function metric()
    {
        return $this->belongsTo(EvaluationMetric::class, 'evaluation_metric_id');
    }

    public function responsibleUser()
    {
        return $this->belongsTo(\App\User::class, 'responsible_user_id');
    }
}