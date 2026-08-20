<?php

namespace App\Http\Controllers;

use App\Approvals;
use App\Models\Auth\Role;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function add(Request $request, $stage)
	{
		$role = Role::query()
			->where('guard_name', 'web')
			->findOrFail($request->role_id);

		$lastApproval = Approvals::where('for', $request->for)
			->where('stage', $stage)
			->where(function ($query) use ($role) {
				$query->where('role_id', $role->id)
					->orWhere('role_group_name', $role->name);
			})
			->orderByDesc('level')
			->first();

		$approval = new Approvals;

		$approval->title = $request->name;
		$approval->for = $request->for;
		$approval->stage = $stage;
		$approval->role_id = $role->id;
		$approval->role_group_name = $role->name;
		$approval->inventory_location_id = getCurrentUserLocation()->id;
		$approval->level = isset($lastApproval->level) ? intval($lastApproval->level) + 1 : 1;
		$approval->save();

		return redirect()->back()->with('success', 'New Approval added to '.$stage);
	}

	public function edit(Request $request, $id)
	{
		$role = Role::query()
			->where('guard_name', 'web')
			->findOrFail($request->role_id);

		$approval = Approvals::findOrFail($id);

		$approval->title = $request->name;
		$approval->role_id = $role->id;
		$approval->role_group_name = $role->name;
		$approval->level = $request->level;
		$approval->save();

		return redirect()->back()->with('success', 'Approval edited');
	}

	public function remove(Request $request, $id)
	{
		return redirect()->back()->with('success', 'Approval removed');
	}
}
