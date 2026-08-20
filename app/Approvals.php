<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Approvals extends Model implements Auditable
{
	use HasUuids;
	use \OwenIt\Auditing\Auditable;
	public function user_roles()
	{
		// Use Spatie role group name (role_id legacy column mapping no longer supported)
		if (empty($this->role_group_name)) {
			// If role_group_name is not set, return empty collection
			return collect([]);
		}

		$users = getActiveUsersByRole($this->role_group_name);

		return $users->map(function ($user) {
			return (object) [
				'id' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
			];
		});
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
