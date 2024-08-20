<?php

namespace App\Http\Controllers;

use App\UserRole;
use Illuminate\Http\Request;

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
		foreach($request->roles as $role){
			$user_role = new UserRole;
			$user_role->user_id = $user_id;
			$user_role->role_id = $role;
			$user_role->save();
		}
		
		return redirect()->back()->with('success', 'User Role(s) has been added!');
	}

	public function remove(Request $request, $id){
		UserRole::find($id)->delete();

		return redirect()->back()->with('success', 'User Role has been deleted!');
	}
}
