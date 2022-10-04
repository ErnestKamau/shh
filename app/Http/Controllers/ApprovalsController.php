<?php

namespace App\Http\Controllers;

use App\Approvals;
use Illuminate\Http\Request;

class ApprovalsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
	}

	public function add(Request $request, $stage){
		$lastApproval = Approvals::where('for', $request->for)->where('stage', $stage)
			->where('role_id', $request->role_id)->first();

		$approval = new Approvals;

		$approval->title = $request->name;
		$approval->for = $request->for;
		$approval->stage = $stage;
		$approval->role_id = $request->role_id;
		$approval->inventory_location_id = getCurrentUserLocation()->id;
		$approval->level = isset($lastApproval->level) ? intval($lastApproval->level)+1 : 1;
		$approval->save();

		return redirect()->back()->with('success', 'New Approval added to '.$stage);
	}

	public function edit(Request $request, $id){

		$approval = Approvals::find($id);

		$approval->title = $request->name;
		$approval->role_id = $request->role_id;
		$approval->level = $request->level;
		$approval->save();

		return redirect()->back()->with('success', 'Approval edited');
	}

	public function remove(Request $request, $id){
		return redirect()->back()->with('success', 'Approval removed from '.$stage);
	}
}
