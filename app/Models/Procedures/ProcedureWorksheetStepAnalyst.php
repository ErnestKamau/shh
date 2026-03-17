<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Model;

class ProcedureWorksheetStepAnalyst extends Model
{
    protected $fillable = [
        'batch_id',
        'analyte_id',
        'procedure_worksheet_id',
        'procedure_worksheet_step_id',
        'analyst_ids',
    ];

    protected function casts(): array
    {
        return [
            'analyst_ids' => 'array',
        ];
    }
}

