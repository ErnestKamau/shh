<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormulaStep extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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
        'step_config',
        'table_mode',
        'row_driver',
        'row_driver_filters',
        'allow_manual_rows',
        'analyte_id',
    ];

    protected $casts = [
        'lookup_config' => 'array',
        'step_config' => 'array',
        'row_driver_filters' => 'array',
        'allow_manual_rows' => 'boolean',
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
            'static_text' => 'Static Text',
            'checkbox' => 'Checkbox',
            'custom_table' => 'Custom Table',
        ];
    }

    public function tableColumns(): HasMany
    {
        return $this->hasMany(FormulaStepTableColumn::class, 'formula_step_id')->orderBy('order');
    }

    public function staticRows(): HasMany
    {
        return $this->hasMany(FormulaStepTableStaticRow::class, 'formula_step_id')->orderBy('order');
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

    public function isStaticText(): bool
    {
        return $this->step_type === 'static_text';
    }

    public function isCheckbox(): bool
    {
        return $this->step_type === 'checkbox';
    }

    public function isCustomTable(): bool
    {
        return $this->step_type === 'custom_table';
    }

    public function isCalculable(): bool
    {
        return in_array($this->step_type, ['input', 'derived', 'lookup', 'parameter_result'], true);
    }

    public function isExpressionVariable(): bool
    {
        return in_array($this->step_type, ['input', 'derived', 'lookup', 'parameter_result', 'checkbox'], true);
    }

    public function staticTextContent(): string
    {
        $config = is_array($this->step_config) ? $this->step_config : [];

        return (string) ($config['content'] ?? $this->description ?? '');
    }
}
