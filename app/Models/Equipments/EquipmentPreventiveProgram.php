<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentPreventiveProgram extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'equipment_preventive_programs';

    protected $fillable = [
        'name',
        'program_date',
        'description',
        'status',
    ];

    protected $casts = [
        'program_date' => 'date',
    ];

    public function maintenances()
    {
        return $this->hasMany(EquipmentPreventiveMaintenance::class, 'equipment_preventive_program_id');
    }
}
