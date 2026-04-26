<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class ProcedureWorksheetStepAnalyst extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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

