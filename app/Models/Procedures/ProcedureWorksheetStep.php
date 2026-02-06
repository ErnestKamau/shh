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
    ];

    public function worksheet()
    {
        return $this->belongsTo(ProcedureWorksheet::class, 'procedure_worksheet_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'default_equipment_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'default_analyst_id');
    }

    public function getMeasurandsAttribute()
    {
        if (empty($this->default_measurand_ids)) {
            return collect([]);
        }
        return \App\ReportingUnit::whereIn('id', $this->default_measurand_ids)->get();
    }
}
