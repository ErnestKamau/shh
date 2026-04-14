<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

	public function edit_departments(Request $request, $user_id, $role){
		$submittedDepartments = $request->has('departments') ? $request->departments : [];

		\App\UserDepartmentalApproval::where('role_id', $role)->where('user_id', $user_id)->delete();

		foreach($submittedDepartments as $d){
			$departmentA = new \App\UserDepartmentalApproval;
			$departmentA->role_id = $role;
			$departmentA->user_id = $user_id;
			$departmentA->department_id = $d;
			$departmentA->save();
		}

		return redirect()->back()->with('success', 'Approval Departments have been updated.');
	}

	public function add(Request $request, $user_id){
		$user = User::find($user_id);
		if (! isset($user->id)) {
			return redirect()->back()->with('error', 'User not found.');
		}

		$roles = Role::query()
			->where('guard_name', 'web')
			->whereIn('id', (array) $request->roles)
			->pluck('name')
			->toArray();

		if (! empty($roles)) {
			$user->assignRole($roles);
		}
		
		return redirect()->back()->with('success', 'User Role(s) has been added!');
	}

	public function remove(Request $request, $id){
		$userId = (int) $request->input('user_id');
		if ($userId < 1) {
			$userId = (int) DB::table('user_roles')->where('id', $id)->value('user_id');
		}
		if ($userId < 1) {
			$userId = (int) DB::table('model_has_roles')
				->where('role_id', $id)
				->where('model_type', User::class)
				->value('model_id');
		}

		$user = User::find($userId);

		if (! isset($user->id)) {
			return redirect()->back()->with('error', 'User not found.');
		}

		$role = Role::query()->where('guard_name', 'web')->find($id);
		if (isset($role->id)) {
			$user->removeRole($role->name);
		}

		return redirect()->back()->with('success', 'User Role has been deleted!');
	}
}
