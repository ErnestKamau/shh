<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Model;

class CapturedProcedureConfigValue extends Model
{
    protected $fillable = [
        'captured_result_id',
        'procedure_worksheet_id',
        'procedure_config_field_id',
        'value',
    ];
}

