<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentAnnualProgram extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'equipment_annual_programs';

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
        return $this->hasMany(EquipmentAnnualMaintenance::class, 'equipment_annual_program_id');
    }
}
