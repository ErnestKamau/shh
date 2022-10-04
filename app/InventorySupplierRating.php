<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventorySupplierRating extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function inventory_item()
	{
		return $this->belongsTo('App\InventoryItem', 'inventory_item_id');
	}

	public function creator(){
		return $this->belongsTo('App\User', 'rating_by');
	}
}
