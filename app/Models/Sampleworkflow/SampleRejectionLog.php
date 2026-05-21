<?php

namespace App\Models\Sampleworkflow;

use App\Models\CRM\CustomerNotification;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SampleRejectionLog extends Model
{
    use HasUuids;

    public const INTEGRITY_NOTICE = 'The above sample has not met the criteria of sample integrity for the tests requested. Analysis of such sample will yield unreliable results. Therefore, we cannot receive the samples for further analysis.';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'submission_form_instance_id',
        'sample_submission_request_id',
        'request_no',
        'client_name',
        'date_sample_received',
        'type_of_sample',
        'number_of_samples',
        'reasons',
        'integrity_notice',
        'rejected_by',
        'rejected_at',
        'pdf_path',
        'internal_comment',
    ];

    protected function casts(): array
    {
        return [
            'date_sample_received' => 'date',
            'reasons' => 'array',
            'rejected_at' => 'datetime',
            'number_of_samples' => 'integer',
        ];
    }

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function sampleSubmissionRequest(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function customerNotifications(): MorphMany
    {
        return $this->morphMany(CustomerNotification::class, 'entity');
    }
}
