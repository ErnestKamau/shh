<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapturedProcedureValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'captured_result_id',
        'procedure_worksheet_step_id',
        'value',
        'equipment_ids',
        'measurand_ids',
        'analyst_ids',
    ];

    protected $casts = [
        'equipment_ids' => 'array',
        'measurand_ids' => 'array',
        'analyst_ids'   => 'array',
    ];

    public function capturedResult()
    {
        return $this->belongsTo(\App\CapturedResult::class);
    }

    public function procedureWorksheetStep()
    {
        return $this->belongsTo(\App\Models\Procedures\ProcedureWorksheetStep::class);
    }
}
