<?php

namespace App\Http\Controllers;

use App\EntityApproval;
use Illuminate\Http\Request;

class ExternalApprovalController extends Controller
{
	public function approve($link_key, $type, $userid){
		if($type == "requisition"){
			$approval = EntityApproval::where('link_key', $link_key)->where('user_id', $userid)->first();
			$ReqController = new \App\Http\Controllers\RequisitionController;

			if(!isset($approval->id)){
				return redirect()->back()->with('error', 'Approval not found');
			}

			$req = $approval->entity;
			$title = "Approval for ".$req->request_type." - ".$req->request_code;

			if($approval->status == "Approved"){
				$action = 'already';
				
				return view('blank', compact('title', 'type', 'action', 'req'));
			}

			$obj = [
				'approve_this'=>1,
				'approval_id'=>$approval->id,
				'user_id'=>$userid
			];

			$request = new Request($obj);

			\Auth::loginUsingId($userid);

			$req = $ReqController->update($request, $approval->model, $approval->model_id, true);
			$action = "approve";

			\Auth::logout();

			return view('blank', compact('title', 'type', 'action', 'req'));
		}

		if($type == "workorder"){
			return view('blank', compact('title', 'type', 'action', 'req'));
		}
	}

	public function reject(Request $request, $link_key, $type, $userid){
		$approval = EntityApproval::where('link_key', $link_key)->where('user_id', $userid)->first();
		$ReqController = new \App\Http\Controllers\RequisitionController;

		if(!isset($approval->id)){
			return redirect()->back()->with('error', 'Approval not found');
		}

		$req = $approval->entity;
		$title = "Rejection for ".$req->request_type." - ".$req->request_code;

		if($approval->status == "Approved"){
			$action = 'already';
			
			return view('blank', compact('title', 'type', 'action', 'req'));
		}

		$req = \App\RequestEntity::find($approval->model_id);
		$isComplete = false;

		if($request->has('reject_reason')){
			$obj = [
				'reject_reason'=>$request->reject_reason,
				'approval_id'=>$approval->id,
				'user_id'=>$userid
			];

			\Auth::loginUsingId($userid);

			$RQ = new Request($obj);
			$req = $ReqController->update($RQ, $approval->model, $approval->model_id, true);
			$isComplete = true;

			\Auth::logout();
		}

		$action = "reject";

		return view('blank', compact('title', 'type', 'action', 'req', 'isComplete'));
	}

	public function recheck(Request $request, $link_key, $type, $userid){
		// return json_encode($request->all());
		$approval = EntityApproval::where('link_key', $link_key)->where('user_id', $userid)->first();
		$ReqController = new \App\Http\Controllers\RequisitionController;

		if(!isset($approval->id)){
			return redirect()->back()->with('error', 'Approval not found');
		}

		$req = $approval->entity;
		$title = $req->request_type." - ".$req->request_code." returned";

		if($approval->status == "Approved"){
			$action = 'already';
			
			return view('blank', compact('title', 'type', 'action', 'req'));
		}

		$req = \App\RequestEntity::find($approval->model_id);
		$isComplete = false;

		if($request->has('recheck_reason')){
			$obj = [
				'recheck_reason'=>$request->recheck_reason,
				'approval_id'=>$approval->id,
				'user_id'=>$userid
			];

			\Auth::loginUsingId($userid);

			$RQ = new Request($obj);
			$req = $ReqController->update($RQ, $approval->model, $approval->model_id, true);
			$isComplete = true;

			\Auth::logout();
		}

		$action = "recheck";

		return view('blank', compact('title', 'type', 'action', 'req', 'isComplete'));
	}
}
