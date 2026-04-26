<?php

namespace App\Models\Procedures;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;

class CapturedProcedureConfigValue extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'captured_result_id',
        'procedure_worksheet_id',
        'procedure_config_field_id',
        'value',
    ];

    protected $casts = [
        'value' => 'encrypted',
    ];
}

