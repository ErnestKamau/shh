<?php

namespace App\Models\Equipments\Logbook;

use App\Enums\LogEntryWorksheet\LogEntryColumnType;
use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentLogbookColumn extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'equipment_id',
        'label',
        'key',
        'column_type',
        'input_data_type',
        'expression',
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

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function columnTypeEnum(): LogEntryColumnType
    {
        return LogEntryColumnType::from($this->column_type);
    }
}
