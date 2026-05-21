<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class EquipmentMaintenanceRegister extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_maintenance_registers';

    protected $fillable = [
        'equipment_id',
        'year',
        'service_provider',
        'service_type',
        'cost_usd',
        'cost_tzs',
    ];

    protected $casts = [
        'cost_usd' => 'decimal:2',
        'cost_tzs' => 'decimal:2',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
