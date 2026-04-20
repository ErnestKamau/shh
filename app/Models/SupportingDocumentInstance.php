<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\User;

class SupportingDocumentInstance extends Model
{
    protected $fillable = [
        'supporting_document_template_id',
        'template_version',
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

    public function values(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstanceValue::class)->with('element');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
