<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RequestWorkflowForm extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'form_type',
        'sample_header_id',
        'sample_submission_request_id',
        'submission_form_instance_id',
        'batch_code',
        'request_reference',
        'payload',
        'pdf_path',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'submitted_at' => 'datetime',
    ];
}
