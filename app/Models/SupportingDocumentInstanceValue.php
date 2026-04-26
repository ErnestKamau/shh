<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportingDocumentInstanceValue extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'supporting_document_instance_id',
        'supporting_document_element_id',
        'value',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SupportingDocumentInstance::class, 'supporting_document_instance_id');
    }

    public function element(): BelongsTo
    {
        return $this->belongsTo(SupportingDocumentElement::class, 'supporting_document_element_id');
    }
}
