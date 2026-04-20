<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class SupportingDocumentTemplate extends Model
{
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
}
