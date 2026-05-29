<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFormInstanceAttachment extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'submission_form_instance_id',
        'file_path',
        'original_name',
        'uploaded_by',
        'attachment_type',
        'attachment_heading',
        'description',
    ];

    public function submissionFormInstance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\User::class, 'uploaded_by');
    }

    public function getFileNameAttribute()
    {
        return $this->original_name;
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
