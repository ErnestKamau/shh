<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class Role extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

	public function users()
	{
		return $this->hasMany('App\UserRole');
	}

	public function getUsersByRole(){
		return User::role($this->name)
			->orderBy('name')
			->where('users.active', 1)
			->get();
	}
}
