<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SystemConfiguration extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'value' => 'encrypted',
    ];

	use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'configuration_type_id',
        'value',
        'key',
        'status',
    ];

    protected $table = 'system_configurations';
}
