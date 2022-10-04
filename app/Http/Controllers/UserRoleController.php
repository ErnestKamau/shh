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
