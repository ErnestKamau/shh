<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryDepartment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function inventory_items(){
		return $this->hasMany('App\InventoryItem');
	}
}
