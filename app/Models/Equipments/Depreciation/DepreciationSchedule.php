<?php

namespace App\Models\Equipments\Depreciation;

use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'depreciation_schedules';

    protected $fillable = [
        'depreciation_schedule_version_id',
        'equipment_id',
        'frequency',
        'period_label',
        'period_date',
        'period_index',
        'opening_book_value',
        'depreciation_amount',
        'accumulated_depreciation',
        'closing_book_value',
        'is_posted',
    ];

    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'opening_book_value' => 'decimal:2',
            'depreciation_amount' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'closing_book_value' => 'decimal:2',
            'is_posted' => 'boolean',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(DepreciationScheduleVersion::class, 'depreciation_schedule_version_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
