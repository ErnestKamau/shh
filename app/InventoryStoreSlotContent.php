<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryStoreSlotContent extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function item()
	{
		return $this->belongsTo('App\InventoryItem', 'inventory_item_id');
	}

	public function slot()
	{
		return $this->belongsTo('App\InventoryStoreSlot', 'inventory_store_slot_id');
	}
}
