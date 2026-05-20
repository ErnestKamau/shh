<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class EquipmentReplacementPlanItem extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_replacement_plan_items';

    protected $fillable = [
        'equipment_replacement_plan_id',
        'equipment_id',
        'equipment_name',
        'scheduled_year',
        'location',
        'remark',
    ];

    public function plan()
    {
        return $this->belongsTo(EquipmentReplacementPlan::class, 'equipment_replacement_plan_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }
}
