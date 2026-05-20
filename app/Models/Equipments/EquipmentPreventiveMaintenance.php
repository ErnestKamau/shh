<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class EquipmentPreventiveMaintenance extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_preventive_maintenances';

    protected $fillable = [
        'equipment_id',
        'equipment_preventive_program_id',
        'scheduled_month',   // int 1-12: which month in the maintenance period year
        'is_serviced',       // bool: has been maintained/calibrated this year
        'serviced_date',     // date: when serviced
        'notes',             // optional note
    ];

    protected $casts = [
        'is_serviced'    => 'boolean',
        'serviced_date'  => 'date',
        'scheduled_month' => 'integer',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function program()
    {
        return $this->belongsTo(EquipmentPreventiveProgram::class, 'equipment_preventive_program_id');
    }
}
