<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryLocationUser extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function locations(){
		return $this->hasMany('App\InventoryLocation');
	}

}
