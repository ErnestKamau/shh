<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\User;

class SupportingDocumentTemplate extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'document_code',
        'title',
        'subtitle',
        'description',
        'version',
        'is_published',
        'is_active',
        'company_id',
        'created_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(SupportingDocumentSection::class)->orderBy('sort_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Requests that have selected this template.
     *
     * @return BelongsToMany<SampleSubmissionRequest, $this>
     */
    public function sampleSubmissionRequests(): BelongsToMany
    {
        return $this->belongsToMany(
            SampleSubmissionRequest::class,
            'sample_submission_request_supporting_document_templates',
            'supporting_document_template_id',
            'sample_submission_request_id'
        )->withTimestamps();
    }
}
