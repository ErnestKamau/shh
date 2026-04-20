<?php

namespace App\Models;

use App\SampleHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\User;

class SupportingDocumentInstance extends Model
{
    protected $fillable = [
        'supporting_document_template_id',
        'template_version',
        'sample_submission_request_id',
        'sample_header_id',
        'status',
        'submitted_at',
        'created_by',
    ];

    protected $casts = [
        'template_version' => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SupportingDocumentTemplate::class, 'supporting_document_template_id');
    }

    /**
     * @return BelongsTo<SampleSubmissionRequest, $this>
     */
    public function sampleSubmissionRequest(): BelongsTo
    {
        return $this->belongsTo(SampleSubmissionRequest::class, 'sample_submission_request_id');
    }

    /**
     * @return BelongsTo<SampleHeader, $this>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(SampleHeader::class, 'sample_header_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstanceValue::class)->with('element');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
