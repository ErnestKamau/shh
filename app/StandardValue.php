<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StandardValue extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'standard_values';
}
