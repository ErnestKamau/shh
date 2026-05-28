<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class FormulaStepTableColumn extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'formula_step_id',
        'label',
        'key',
        'column_type',
        'input_data_type',
        'expression',
        'model_tied_to',
        'dataset_config',
        'order',
        'is_required',
        'help_text',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
            'dataset_config' => 'array',
        ];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(FormulaStep::class, 'formula_step_id');
    }

    /**
     * @return array<string, string>
     */
    public static function datasetPresetOptions(): array
    {
        return [
            'equipments' => 'Equipment',
            'users' => 'Users',
            'methods' => 'Methods',
            'analytes' => 'Analytes',
        ];
    }
}
