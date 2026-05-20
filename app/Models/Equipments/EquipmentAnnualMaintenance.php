<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class EquipmentAnnualMaintenance extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_annual_maintenances';

    protected $fillable = [
        'equipment_id',
        'equipment_annual_program_id',
        'serviced_date',
        'status',
        'next_service',
        'remark',
    ];

    protected $casts = [
        'serviced_date' => 'date',
        'next_service' => 'date',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function program()
    {
        return $this->belongsTo(EquipmentAnnualProgram::class, 'equipment_annual_program_id');
    }
}
