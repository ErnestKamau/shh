<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;

class SampleSubmissionRequestSuspect extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sample_submission_request_id',
        'serial_number',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'date_of_birth',
        'nationality',
        'id_passport_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function request()
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }
}