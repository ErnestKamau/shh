<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;

class SampleSubmissionRequestRequestedAnalysis extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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