<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UnitOfMeasureConversion extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = "uom_conversions";
}
