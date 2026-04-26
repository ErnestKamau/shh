<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class SerWorksheetStep extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'step',
        'is_active',
        'default_equipment_id',
        'default_analyst_id',
        'default_measurand_ids',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_measurand_ids' => 'array',
    ];

    public function equipment()
    {
        return $this->belongsTo(\App\Models\Equipments\Equipment::class, 'default_equipment_id');
    }

    public function analyst()
    {
        return $this->belongsTo(\App\User::class, 'default_analyst_id');
    }
}
