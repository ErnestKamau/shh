<?php

namespace App\Http\Controllers\WorkOrder;

use App\Models\Workorder\WorkorderApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


use App\Http\Controllers\Controller;
class WorkorderApprovalController extends Controller
{
  function __construct()
	{
		$this->middleware('auth');
	}

	function change_approver(Request $request, $id){
		$approval = WorkorderApproval::where('workorder_id', $id)->where('approval', $request->approval)->first() ?? new WorkorderApproval;

		$uParts = explode("^^", $request->user);

		$approval->approval = $request->approval;
		$approval->workorder_id = $request->id;
		$approval->name = $uParts[0];
		$approval->email = $uParts[1];
		$approval->phone = $uParts[2];
		$approval->user_key = Hash::make($request->selectedUser.(time()*rand(0, 100000000000)));
		$approval->save();

		return [
			"status"=>true
		];
	}

	function confirm_approval(Request $request, $key, $action){
		$approval = WorkorderApproval::where('user_key', $key);
		if($action=="approve"){
			$approval->status == "completed";
			$approval->action_date == \Carbon\Carbon::now();
			$approval->save();
		}
		else{
			$approval->status == "rejected";
			$approval->action_date == \Carbon\Carbon::now();
			$approval->save();
		}
	}
}
