<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentUsage extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
	protected $table = 'equipment_usage';

	public function operator(){
		return \App\User::find($this->operator);
	}

	public function sample(){
		return \App\SampleHeader::find($this->sample_header);
	}
}
