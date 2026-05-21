<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentMaintenanceProgram extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'equipment_maintenance_programs';

    protected $fillable = [
        'type',
        'name',
        'program_date',
        'description',
        'status',
    ];

    protected $casts = [
        'program_date' => 'date',
    ];
}
