<?php

namespace App\Models\Registry;

use App\Models\Registry\Concerns\ScopesByCompany;
use Database\Factories\Registry\RegistryRequestCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegistryRequestCategory extends Model
{
    /** @use HasFactory<RegistryRequestCategoryFactory> */
    use HasFactory;
    use HasUuids;
    use ScopesByCompany;
    use SoftDeletes;

    protected static function newFactory(): RegistryRequestCategoryFactory
    {
        return RegistryRequestCategoryFactory::new();
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'workflow_definition_id',
        'default_priority',
        'metadata_schema',
        'is_active',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'metadata_schema' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function workflowDefinition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_definition_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RegistryRequest::class, 'request_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
