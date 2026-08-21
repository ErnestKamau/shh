<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EntityApproval extends Model implements Auditable
{
	use HasUuids;
	use \OwenIt\Auditing\Auditable;

	protected $keyType = 'string';

	public $incrementing = false;

	public function user()
	{
		return User::find($this->user_id);
	}
	
	public function entity(){
		return $this->hasOne(RequestEntity::class, 'id', 'model_id');
	}
}
