<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserRole extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function role()
	{
		return $this->belongsTo('App\Role');
	}
}
