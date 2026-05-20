<?php

namespace App\Models\Registry;

use App\Models\Registry\Concerns\ScopesByCompany;
use Database\Factories\Registry\WorkflowDefinitionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkflowDefinition extends Model
{
    /** @use HasFactory<WorkflowDefinitionFactory> */
    use HasFactory;
    use HasUuids;
    use ScopesByCompany;
    use SoftDeletes;

    protected static function newFactory(): WorkflowDefinitionFactory
    {
        return WorkflowDefinitionFactory::new();
    }

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'company_id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('sequence');
    }

    public function transitions(): HasMany
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(RegistryRequestCategory::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
