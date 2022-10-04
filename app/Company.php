<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Company extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function labs(){
    return $this->hasMany('App\Lab');
  }
}
