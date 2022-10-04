<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Approvals  extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function user_roles()
	{
		return UserRole::join('users as u', 'u.id', '=', 'user_roles.user_id')
			->selectRaw('u.id, u.name, u.email')
			->where('user_roles.role_id', $this->role_id)->get();
	}

	public function entity_approval($model, $model_id){
		return EntityApproval::where('approval_id', $this->id)->where('model', $model)->where('model_id', $model_id)->first();
	}

	public function is_pending($model, $model_id){
		return EntityApproval::where('approval_id', $this->id)->where('model', $model)
			->where('status', "Pending")->where('model_id', $model_id)->first();
	}

	public function approver($model, $model_id){
		return EntityApproval::join('users as u', 'u.id', '=', 'entity_approvals.user_id')->selectRaw('u.*')
			->where('approval_id', $this->id)->where('model', $model)->where('model_id', $model_id)->first();
	}
}
