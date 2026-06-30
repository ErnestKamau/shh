<?php

namespace App\Models;

use App\AnalysisElements;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleSubmissionRequestRequestedAnalysis extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_submission_request_id',
        'sample_type_id',
        'analysis_type_id',
        'analysis_element_id',
        'analysis_key',
        'analysis_label',
        'number_of_samples',
    ];

    protected function casts(): array
    {
        return [
            'number_of_samples' => 'integer',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function analysisElement(): BelongsTo
    {
        return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
    }
}
