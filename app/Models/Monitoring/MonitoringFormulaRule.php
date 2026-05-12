<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringFormulaRule extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'name',
        'output_key',
        'expression',
        'pass_condition_expression',
        'meta',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplate::class, 'template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(MonitoringTemplateField::class, 'formula_rule_id');
    }
}
