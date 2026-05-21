<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Model;

class EquipmentReplacementPlan extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'equipment_replacement_plans';

    protected $fillable = [
        'name',
        'start_year',
        'end_year',
    ];

    public function items()
    {
        return $this->hasMany(EquipmentReplacementPlanItem::class, 'equipment_replacement_plan_id');
    }

    /**
     * Compute array of string years like ["2025/2026", "2026/2027", ...]
     */
    public function getYearsRange(): array
    {
        $years = [];
        for ($y = $this->start_year; $y <= $this->end_year; $y++) {
            $years[] = $y . '/' . ($y + 1);
        }
        return $years;
    }
}
