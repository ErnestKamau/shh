<?php

namespace App;

use App\Models\System\SystemConfiguration;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchAttachment extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'batch_attachments';
    
    protected $appends = ['uploaduser', 'attachtypename', 'file_name', 'file_type'];

    public function getUploadUserAttribute()
    {
        return User::find($this->uploaded_by)->name ?? '';
    }

    public function getAttachtypenameAttribute()
    {
        return SystemConfiguration::find($this->attachment_type)->value ?? 'General';
    }

    public function getFileNameAttribute()
    {
        if (!$this->attachment_url) {
            return null;
        }
        return basename($this->attachment_url);
    }

    /**
     * Title without the trailing report language marker, e.g. "Test Report · 261008055-R02 (EN)".
     */
    public function getDisplayTitleAttribute(): string
    {
        return trim((string) preg_replace('/\s*\((EN|AR|PT)\)\s*$/i', '', (string) $this->title));
    }

    /**
     * Human-readable report language parsed from the trailing title marker, if any.
     */
    public function getReportLanguageAttribute(): ?string
    {
        if (! preg_match('/\((EN|AR|PT)\)\s*$/i', (string) $this->title, $matches)) {
            return null;
        }

        return [
            'EN' => 'English',
            'AR' => 'Arabic',
            'PT' => 'Portuguese',
        ][strtoupper($matches[1])];
    }

    public function getFileTypeAttribute()
    {
        if (!$this->attachment_url) {
            return null;
        }
        return strtoupper(pathinfo($this->attachment_url, PATHINFO_EXTENSION));
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get all annotations for this attachment.
     */
    public function annotations()
    {
        return $this->hasMany(\App\Models\BatchAttachmentAnnotation::class, 'batch_attachment_id');
    }

    /**
     * Captured results that have been linked to this attachment (Result Report type).
     */
    public function capturedResults()
    {
        return $this->hasMany(\App\CapturedResult::class, 'batch_attachment_id');
    }
}
