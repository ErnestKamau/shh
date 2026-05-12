<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringTemplateField extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'formula_rule_id',
        'field_key',
        'label',
        'field_type',
        'is_required',
        'is_readonly',
        'sort_order',
        'field_config',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_readonly' => 'boolean',
        'field_config' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplate::class, 'template_id');
    }

    public function formulaRule(): BelongsTo
    {
        return $this->belongsTo(MonitoringFormulaRule::class, 'formula_rule_id');
    }
}
