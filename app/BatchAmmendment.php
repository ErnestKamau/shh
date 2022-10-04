<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BatchAmmendment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'batch_ammendments';
}
