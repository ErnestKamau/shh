<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryOrderItem extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public function orders(){
		return $this->belongsTo('App\InventoryOrder');
	}
}
