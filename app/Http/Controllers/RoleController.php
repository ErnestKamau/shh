<?php

namespace App\Http\Controllers;

use App\Models\Lab\Qualification;
use App\Role;
use App\Models\Personnel\RoleCertification;

use Illuminate\Http\Request;

class RoleController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function index(){
		$roles = Role::orderBy('name', 'asc')->where('company_id', getUserCompany())->get();
		return view('layouts.personnel.roles.index', compact('roles'));
	}

	public function add(Request $request){
		$role = new Role;
		$role->name = $request->name;
		$role->description = $request->description;
		$role->level = $request->level;
		$role->company_id = getUserCompany();
		$role->active = $request->active ?? 0;
		$role->save();

		return redirect()->back()->with('success', 'Role added successfully');
	}

	public function show(Request $request, $id){

		$role = Role::find($id);
		$certifications = RoleCertification::where('role_id',$id)->get();
		$certifications_list = Qualification::where('module_code',1)->get();
		$permissions = json_decode($role->permissions, true);
		return view('layouts.personnel.roles.show', compact('role', 'permissions','certifications_list','certifications'));
	}

	public function save_roles(Request $request, $id){
		// return "<pre>".json_encode($request->all(), JSON_PRETTY_PRINT)."</pre>";
		$role = Role::find($id);
		$role->permissions = json_encode($request->permissions);
		$role->save();

		return redirect()->back()->with('success', 'Role permissions were set successfully');
	}

	public function edit(Request $request, $id){
		$role = Role::find($id);
		// $role->name = $request->name;
		$role->active = $request->active ?? 0;
		$role->name = $request->name;
		$role->description = $request->description;
		$role->company_id = getUserCompany();
		$role->level = $request->level;

		$role->save();

		return redirect()->back()->with('success', 'Role edit successfully');
	}
}
