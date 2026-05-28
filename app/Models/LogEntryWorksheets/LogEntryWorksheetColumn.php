<?php

namespace App\Models\LogEntryWorksheets;

use App\Enums\LogEntryWorksheet\LogEntryColumnType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class LogEntryWorksheetColumn extends Model implements Auditable
{
    use HasFactory;
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'log_entry_worksheet_id',
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

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(LogEntryWorksheet::class, 'log_entry_worksheet_id');
    }

    public function columnTypeEnum(): LogEntryColumnType
    {
        return LogEntryColumnType::from($this->column_type);
    }
}
