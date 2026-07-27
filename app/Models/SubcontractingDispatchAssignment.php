<?php

namespace App\Models;

use App\AnalysisElements;
use App\Lab;
use App\SampleHeader;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractingDispatchAssignment extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_submission_request_id',
        'lab_id',
        'analysis_element_id',
        'sample_header_id',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }

    public function analysisElement(): BelongsTo
    {
        return $this->belongsTo(AnalysisElements::class, 'analysis_element_id');
    }

    public function sampleHeader(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }
}
