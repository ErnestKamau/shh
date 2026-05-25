<?php

namespace App\Models\Equipments\Depreciation;

use App\Models\Equipments\Equipment;
use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationLedgerEntry extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'depreciation_ledger_entries';

    protected $fillable = [
        'equipment_id',
        'equipment_depreciation_config_id',
        'depreciation_schedule_version_id',
        'depreciation_schedule_id',
        'period_label',
        'period_date',
        'method_code',
        'frequency',
        'opening_book_value',
        'depreciation_amount',
        'accumulated_depreciation',
        'closing_book_value',
        'version_number',
        'generated_by',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'opening_book_value' => 'decimal:2',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'closing_book_value' => 'decimal:2',
            'calculated_at' => 'datetime',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function config(): BelongsTo
    {
        return $this->belongsTo(EquipmentDepreciationConfig::class, 'equipment_depreciation_config_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
