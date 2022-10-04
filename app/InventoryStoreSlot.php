<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryStoreSlot extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function contents(){
		return $this->hasMany('App\InventoryStoreSlotContent');
	}
}
