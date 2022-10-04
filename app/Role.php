<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	public function users()
	{
		return $this->hasMany('App\UserRole');
	}

	public function getUsersByRole(){
		return UserRole::join('users as u', 'u.id', 'user_roles.user_id')->where('role_id', $this->id)
			->selectRaw('u.*')->get();
	}
}
