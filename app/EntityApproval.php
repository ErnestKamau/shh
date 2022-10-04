<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EntityApproval extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function user()
	{
		return User::find($this->user_id);
	}
}
