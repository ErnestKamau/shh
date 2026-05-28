<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaMandatoryField extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formula_version_id',
        'label',
        'field_type',
        'order',
        'form_placement',
        'help_text',
        'model_tied_to',
        'is_required',
        'field_value_name',
        'field_options',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
            'field_options' => 'array',
        ];
    }

    /**
     * Get the formula version that owns this mandatory field.
     */
    public function formulaVersion(): BelongsTo
    {
        return $this->belongsTo(FormulaVersion::class);
    }

    /**
     * Get the field type options.
     */
    public static function getFieldTypes(): array
    {
        return [
            'input' => 'Text Input',
            'datetime' => 'Date & Time',
            'date' => 'Date',
            'checkbox' => 'Checkbox (options)',
            'dataset_related' => 'Dataset Related',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function formPlacementOptions(): array
    {
        return [
            'top' => 'Top of form (above sample table)',
            'bottom' => 'Bottom of form (below sample table)',
        ];
    }

    /**
     * @return list<string>
     */
    public function checkboxOptionLabels(): array
    {
        $options = $this->field_options['options'] ?? [];

        return is_array($options) ? array_values(array_filter(array_map('strval', $options))) : [];
    }

    /**
     * Get the dataset model options.
     */
    public static function getDatasetModels(): array
    {
        return [
            'equipments' => 'Equipments',
            'users' => 'Users',
            'methods' => 'Methods',
        ];
    }

    /**
     * Scope for ordering by the order column.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
