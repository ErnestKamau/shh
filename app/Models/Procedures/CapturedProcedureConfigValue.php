<?php

namespace App\Models\Procedures;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class CapturedProcedureConfigValue extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'captured_result_id',
        'procedure_worksheet_id',
        'procedure_config_field_id',
        'value',
    ];
}

