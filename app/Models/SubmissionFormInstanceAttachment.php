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

    public function getFileNameAttribute(): ?string
    {
        $original = $this->attributes['original_name'] ?? null;

        if (! is_string($original) || ! preg_match('/^trf-sfi-[0-9a-f-]+\.pdf$/i', $original)) {
            return $original;
        }

        $formNumber = trim((string) ($this->submissionFormInstance?->form_number ?? ''));
        if ($formNumber !== '') {
            return 'Test-Request-Form-'.preg_replace('/[\/\\\\]+/', '-', $formNumber).'.pdf';
        }

        $heading = trim((string) ($this->attributes['attachment_heading'] ?? ''));

        return ($heading !== '' ? str_replace(' ', '-', $heading) : 'Test-Request-Form').'.pdf';
    }

    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return asset('storage/' . $this->file_path);
        }
        return null;
    }
}
