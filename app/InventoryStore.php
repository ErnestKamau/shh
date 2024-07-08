<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryStore extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function slots()
	{
		return $this->hasMany('App\InventoryStoreSlot', 'inventory_store_id');
	}

	public function contacts(){
		return InventoryStoreContact::where('store', $this->id)
			->join('users as u', 'u.id', 'user_id')
			->selectRaw('inventory_store_contacts.id as store_contact_id, u.name, u.email')->orderBy('u.name')->get();
	}

	public function cost_centers(){
		return StoreToCostCenter::where('store_id', $this->id)->orderBy('cost_center')->get();
	}
}
