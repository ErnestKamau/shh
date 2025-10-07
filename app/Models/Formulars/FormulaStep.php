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
    ];

    protected $casts = [
        'lookup_config' => 'array',
    ];

    /**
     * Get the formula version that owns this step.
     */
    public function formulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class);
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
}
