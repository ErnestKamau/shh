<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MaintainanceCalibrationLog extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function overseer(){
		return \App\User::find($this->overseen_by);
	}

	public function editor(){
		return \App\User::find($this->edited_by);
	}
}
