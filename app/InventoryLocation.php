<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryLocation extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function locations(){
		return $this->hasMany('App\InventoryLocation');
	}

  public function users(){
		return User::join('inventory_location_users as ilu', 'ilu.user_id', '=', 'users.id')
			->selectRaw('users.*')
			->where('ilu.inventory_location_id', '=', $this->id)->get();
	}

	public function parent(){
		return \App\InventoryLocation::find($this->inventory_location_id);
	}
}
