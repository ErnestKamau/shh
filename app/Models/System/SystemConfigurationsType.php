<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SystemConfigurationsType extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'configuration_type',
        'description',
        'status',
    ];

    protected $table = 'system_configuration_types';
}
