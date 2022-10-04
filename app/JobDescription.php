<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class JobDescription extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'job_designation_responsibility';
}
