<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formula_version_id',
        'step_number',
        'variable_name',
        'step_type',
        'expression',
        'label',
        'description',
        'lookup_config',
        'analyte_id',
        'is_end_stage',
        'is_end_stage_if_pass',
    ];

    protected $casts = [
        'lookup_config' => 'array',
        'is_end_stage' => 'boolean',
        'is_end_stage_if_pass' => 'boolean',
    ];

    /**
     * Get the formula version that owns this step.
     */
    public function formulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class);
    }

    /**
     * Get the analyte that owns this step.
     */
    public function analyte(): BelongsTo
    {
        return $this->belongsTo(\App\Analyte::class);
    }

    /**
     * Get the step type options.
     */
    public static function getStepTypes(): array
    {
        return [
            'input' => 'User Input',
            'derived' => 'Calculated',
            'lookup' => 'Lookup Value',
            'parameter_result' => 'Parameter Result',
        ];
    }

    /**
     * Check if this step is an input step.
     */
    public function isInput(): bool
    {
        return $this->step_type === 'input';
    }

    /**
     * Check if this step is a derived step.
     */
    public function isDerived(): bool
    {
        return $this->step_type === 'derived';
    }

    /**
     * Check if this step is a lookup step.
     */
    public function isLookup(): bool
    {
        return $this->step_type === 'lookup';
    }

    /**
     * Check if this step is a parameter result step.
     */
    public function isParameterResult(): bool
    {
        return $this->step_type === 'parameter_result';
    }
}
