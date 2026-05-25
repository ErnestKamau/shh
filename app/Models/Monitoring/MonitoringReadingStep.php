<?php

namespace App\Models\Monitoring;

use App\Analyte;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitoringReadingStep extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'template_id',
        'step_number',
        'variable_name',
        'step_type',
        'expression',
        'derived_config',
        'label',
        'description',
        'lookup_config',
        'analyte_id',
        'variable_slug',
    ];

    protected $casts = [
        'lookup_config' => 'array',
        'derived_config' => 'array',
        'step_number' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MonitoringTemplate::class, 'template_id');
    }

    public function analyte(): BelongsTo
    {
        return $this->belongsTo(Analyte::class);
    }

    /**
     * @return array<string, string>
     */
    public static function stepTypeOptions(): array
    {
        return [
            'input' => 'Input',
            'derived' => 'Derived',
            'lookup' => 'Lookup',
            'parameter_result' => 'Parameter Result',
        ];
    }

    public function isInput(): bool
    {
        return $this->step_type === 'input';
    }

    public function isDerived(): bool
    {
        return $this->step_type === 'derived';
    }

    public function isLookup(): bool
    {
        return $this->step_type === 'lookup';
    }

    public function isParameterResult(): bool
    {
        return $this->step_type === 'parameter_result';
    }
}
