<?php

namespace App\Models\Formulars;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaMandatoryField extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'formula_version_id',
        'label',
        'field_type',
        'order',
        'help_text',
        'model_tied_to',
        'is_required',
        'field_value_name',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

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
            'dataset_related' => 'Dataset Related',
        ];
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
