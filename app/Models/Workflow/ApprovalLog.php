<?php

namespace App\Models\Workflow;

use App\SampleHeader;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'workflow_approval_logs';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'sample_id',
        'submission_form_instance_id',
        'approval_id',
        'stage_name',
        'status',
        'remarks',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class, 'approval_id');
    }

    public function sample()
    {
        return $this->belongsTo(SampleHeader::class, 'sample_id');
    }

    public function submissionFormInstance()
    {
        return $this->belongsTo(\App\Models\SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}