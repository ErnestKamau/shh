<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentOperator extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	public function operator(){
		return \App\User::find($this->user_id);
	}
}
