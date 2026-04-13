<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class FeedbackRating extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_feedback_ratings';

    protected $fillable = [
        'customer_feedback_id',
        'evaluation_metric_id',
        'rating',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function feedback()
    {
        return $this->belongsTo(CustomerFeedback::class, 'customer_feedback_id');
    }

    public function metric()
    {
        return $this->belongsTo(EvaluationMetric::class, 'evaluation_metric_id');
    }
}
