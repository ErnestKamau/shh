<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;

class EquipmentDailyLogEntry extends Model
{
    protected $fillable = [
        'equipment_id',
        'company_id',
        'log_date',
        'slot_number',
        'recorded_value',
        'recorded_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'slot_number' => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
