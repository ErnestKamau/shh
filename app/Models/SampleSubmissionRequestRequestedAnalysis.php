<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleSubmissionRequestRequestedAnalysis extends Model
{
    protected $fillable = [
        'sample_submission_request_id',
        'analysis_key',
        'analysis_label',
    ];

    public function request()
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }
}