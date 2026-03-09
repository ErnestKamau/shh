<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedureConfigField extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'procedure_worksheet_id',
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

    public function procedureWorksheet(): BelongsTo
    {
        return $this->belongsTo(ProcedureWorksheet::class);
    }

    public static function getFieldTypes(): array
    {
        return [
            'input' => 'Text',
            'number' => 'Number',
            'checkbox' => 'Checkbox',
            'textarea' => 'Textarea',
            'datetime' => 'Date & Time',
            'date' => 'Date',
            'dataset' => 'Dataset (select from list)',
            'dataset_multiselect' => 'Dataset (multi-select)',
        ];
    }

    /**
     * Get the dataset model options for fields of type dataset or dataset_multiselect.
     *
     * @return array<string, string>
     */
    public static function getDatasetModels(): array
    {
        return [
            'users' => 'Lab Analysts',
            'sample_details' => 'Sample No.',
            'sample_types' => 'Sample Types',
            'methods' => 'Methods',
            'captured_results' => 'Tests',
            'report_formats' => 'Report Formats',
        ];
    }
}
