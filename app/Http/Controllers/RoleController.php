<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }

	public function index(){
		return view('livewire.layout.personnel-app', [
			'componentType' => 'organizational-roles',
			'pageTitle' => 'Organizational Roles',
		]);
	}

	public function add(Request $request){
		$role = new Role();
		$role->name = $request->name;
		$role->description = $request->description;
		$role->level = $request->level;
		$role->company_id = getUserCompany();
		$role->active = $request->active ?? 0;
		$role->guard_name = 'web';
		$role->save();

		return redirect()->back()->with('success', 'Role added successfully');
	}

	public function show(Request $request, $id){
		return view('livewire.layout.personnel-app', [
			'componentType' => 'organizational-role-detail',
			'pageTitle' => 'Role Details',
			'roleId' => (int) $id,
		]);
	}

	public function save_roles(Request $request, $id){
		$role = Role::query()->where('guard_name', 'web')->findOrFail($id);
		$permissionNames = $this->extractPermissionNames((array) $request->permissions);

		foreach ($permissionNames as $permissionName) {
			Permission::query()->firstOrCreate([
				'name' => $permissionName,
				'guard_name' => 'web',
			]);
		}

		$role->syncPermissions($permissionNames);

		return redirect()->back()->with('success', 'Role permissions were set successfully');
	}

	public function edit(Request $request, $id){
		$role = Role::query()->where('guard_name', 'web')->findOrFail($id);
		$role->active = $request->active ?? 0;
		$role->name = $request->name;
		$role->description = $request->description;
		$role->company_id = getUserCompany();
		$role->level = $request->level;

		$role->save();

		return redirect()->back()->with('success', 'Role edit successfully');
	}

	private function extractPermissionNames(array $permissions): array
	{
		$names = [];
		foreach ($permissions as $module => $modulePayload) {
			if (($modulePayload['permission'] ?? 'false') === 'true') {
				$names[] = $module . '.permission';
			}

			foreach (($modulePayload['components'] ?? []) as $component => $actions) {
				foreach ($actions as $action => $value) {
					if ($value === 'true') {
						$names[] = $module . '.components.' . $component . '.' . $action;
					}
				}
			}
		}

		return array_values(array_unique($names));
	}
}
