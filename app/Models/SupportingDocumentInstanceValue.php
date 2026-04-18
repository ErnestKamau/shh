<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportingDocumentInstanceValue extends Model
{
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
