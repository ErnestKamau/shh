<?php

namespace App\Models\LogEntryWorksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class LogEntryWorksheetMandatoryField extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'log_entry_worksheet_id',
        'label',
        'field_type',
        'order',
        'help_text',
        'model_tied_to',
        'is_required',
        'field_value_name',
        'field_options',
        'dataset_config',
        'default_current_date',
        'default_authenticated_user',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'order' => 'integer',
            'field_options' => 'array',
            'dataset_config' => 'array',
            'default_current_date' => 'boolean',
            'default_authenticated_user' => 'boolean',
        ];
    }

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(LogEntryWorksheet::class, 'log_entry_worksheet_id');
    }

    /**
     * Built-in list fields (stored value = record id).
     *
     * @return array<string, string>
     */
    public static function presetLookupTypes(): array
    {
        return [
            'equipments' => 'Equipment',
            'users' => 'User',
            'methods' => 'Method',
            'sample_types' => 'Sample type',
            'analytes' => 'Analyte',
        ];
    }

    public static function isPresetLookupType(string $fieldType): bool
    {
        return array_key_exists($fieldType, self::presetLookupTypes());
    }

    /**
     * @return array<string, string>
     */
    public static function getFieldTypes(): array
    {
        return array_merge([
            'input' => 'Text Input',
            'datetime' => 'Date & Time',
            'date' => 'Date',
            'checkbox' => 'Checkbox (options)',
            'radio' => 'Radio buttons',
        ], self::presetLookupTypes(), [
            'dataset_related' => 'Dataset (custom table lookup)',
        ]);
    }

    public static function presetLookupDescription(string $fieldType): string
    {
        return match ($fieldType) {
            'equipments' => 'Analysts pick from active lab equipment.',
            'users' => 'Analysts pick from active system users.',
            'methods' => 'Analysts pick from analysis methods.',
            'sample_types' => 'Analysts pick from active sample types.',
            'analytes' => 'Analysts pick from active analytes.',
            default => '',
        };
    }
}
