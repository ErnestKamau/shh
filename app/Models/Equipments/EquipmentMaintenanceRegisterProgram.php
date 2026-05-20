<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentMaintenanceRegisterProgram extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'equipment_maintenance_register_programs';

    protected $fillable = [
        'name',
        'program_date',
        'description',
        'status',
    ];

    protected $casts = [
        'program_date' => 'date',
    ];

    public function registers()
    {
        return $this->hasMany(EquipmentMaintenanceRegister::class, 'equipment_maintenance_register_program_id');
    }
}
