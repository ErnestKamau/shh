<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EvaluationMetric extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'crm_evaluation_metrics';

    protected $fillable = [
        'name',
        'prompt_text',
        'max_rating',
        'rating_labels',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
        'max_rating' => 'integer',
        'rating_labels' => 'array',
    ];
}
