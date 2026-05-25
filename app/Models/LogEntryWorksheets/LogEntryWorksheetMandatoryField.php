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
     * @return array<string, string>
     */
    public static function getFieldTypes(): array
    {
        return [
            'input' => 'Text Input',
            'datetime' => 'Date & Time',
            'date' => 'Date',
            'checkbox' => 'Checkbox (options)',
            'radio' => 'Radio buttons',
            'dataset_related' => 'Dataset (table lookup)',
        ];
    }
}
