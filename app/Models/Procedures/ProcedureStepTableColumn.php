<?php

namespace App\Models\Procedures;

use App\Enums\Procedures\ProcedureStepTableColumnType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class ProcedureStepTableColumn extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'procedure_worksheet_step_id',
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
        return $this->belongsTo(ProcedureWorksheetStep::class, 'procedure_worksheet_step_id');
    }

    public function columnTypeEnum(): ProcedureStepTableColumnType
    {
        return ProcedureStepTableColumnType::from($this->column_type);
    }

    /**
     * @return array<string, string>
     */
    public static function datasetPresetOptions(): array
    {
        return array_merge(
            ProcedureConfigField::getDatasetModels(),
            [
                'equipments' => 'Equipment',
                'analytes' => 'Analytes',
            ]
        );
    }
}
