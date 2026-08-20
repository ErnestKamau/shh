<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryStore extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

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
		if (! \Illuminate\Support\Facades\Schema::hasTable('store_to_cost_centers')) {
			return collect();
		}

		return StoreToCostCenter::where('store_id', $this->id)->orderBy('cost_center')->get();
	}
}
