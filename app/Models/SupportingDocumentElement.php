<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportingDocumentElement extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'supporting_document_section_id',
        'element_type',
        'label',
        'name',
        'placeholder',
        'help_text',
        'is_required',
        'is_readonly',
        'default_value',
        'validation_rules',
        'options',
        'conditional_logic',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_readonly' => 'boolean',
        'validation_rules' => 'array',
        'options' => 'array',
        'conditional_logic' => 'array',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SupportingDocumentSection::class, 'supporting_document_section_id');
    }

    public function instanceValues(): HasMany
    {
        return $this->hasMany(SupportingDocumentInstanceValue::class);
    }
}
