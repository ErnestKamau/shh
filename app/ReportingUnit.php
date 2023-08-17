<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ReportingUnit extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public $fillable = ['name', 'active'];
}
