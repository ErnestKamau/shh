<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Model;
use App\Models\Equipments\Equipment;
use App\User;

class ProcedureWorksheetStep extends Model
{
    protected $fillable = [
        'procedure_worksheet_id',
        'step',
        'is_active',
        'default_equipment_id',
        'default_analyst_id',
        'default_measurand_ids',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_measurand_ids' => 'array',
        'default_equipment_id' => 'array',
        'default_analyst_id' => 'array',
    ];

    public function worksheet()
    {
        return $this->belongsTo(ProcedureWorksheet::class, 'procedure_worksheet_id');
    }

    public function getEquipmentAttribute()
    {
        $ids = is_array($this->default_equipment_id) ? $this->default_equipment_id : [];
        if (empty($ids)) {
            return collect([]);
        }
        return Equipment::whereIn('id', $ids)->get();
    }

    public function getAnalystsAttribute()
    {
        $ids = is_array($this->default_analyst_id) ? $this->default_analyst_id : [];
        if (empty($ids)) {
            return collect([]);
        }
        return User::whereIn('id', $ids)->get();
    }

    public function getMeasurandsAttribute()
    {
        if (empty($this->default_measurand_ids)) {
            return collect([]);
        }
        return \App\ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();
    }
}
