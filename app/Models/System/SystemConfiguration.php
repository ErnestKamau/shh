<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SystemConfiguration extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'system_configurations';
}
