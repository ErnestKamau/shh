<?php

namespace App\Http\Controllers;

use App\Lab;
use App\LabSectionApprover;
use App\LabSectionApproverRelationShip;
use App\SampleAnalysisStage;
use Illuminate\Http\Request;
use App\User;

class SampleAnalysisStageController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$sampleAnalysisStage = SampleAnalysisStage::orderBy('name')->get();
		$users = User::where('is_client',0)->where('supplier_id',0)->where('active',1)->get();
		$labs = Lab::where('active',1)->get();
		$approvers = LabSectionApprover::orderBy('created_at','asc')->get();

		return view('layouts.lab.sample-analysis-stages.index', compact('sampleAnalysisStage','users','labs','approvers'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request)
	{
		$sampleAnalysisStage = new SampleAnalysisStage;

		$sampleAnalysisStage->name = $request->name;
    $sampleAnalysisStage->company_id = getUserCompany();
		$sampleAnalysisStage->active = $request->active ?? 0;
		$sampleAnalysisStage->sample_workflow = $request->sample_workflow;
		$sampleAnalysisStage->section_head_id = $request->section_head_id;
		$sampleAnalysisStage->level = $request->level;
		$sampleAnalysisStage->lab_id = $request->lab_id;
		$sampleAnalysisStage->code = $request->code;

		$sampleAnalysisStage->save();

		return redirect()->back()->with('success', 'Sample Analysis Stage Added.');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{

		$sampleAnalysisStage = SampleAnalysisStage::find($id);

		$sampleAnalysisStage->name = $request->name;
    	$sampleAnalysisStage->company_id = getUserCompany();
		$sampleAnalysisStage->active = $request->active ?? 0;
		$sampleAnalysisStage->sample_workflow = $request->sample_workflow;
		$sampleAnalysisStage->section_head_id = $request->section_head_id;
		$sampleAnalysisStage->level = $request->level;
		$sampleAnalysisStage->lab_id = $request->lab_id;
		$sampleAnalysisStage->code = $request->code;
		$sampleAnalysisStage->save();

		return redirect()->back()->with('success', 'Sample Analysis Stage Edited.');
	}
	public function addSectionApproval(Request $request){
		$approver = $request->approver_id == 0 ? new LabSectionApprover() :  LabSectionApprover::find($request->approver_id);
		$approver->user_id = $request->user_id;
		$approver->lab_section_ids = implode(',',$request->section_ids ?? []);
		$approver->title = $request->title;
		$approver->save();
		LabSectionApproverRelationShip::whereIn('lab_section_id',$request->section_ids ?? [])->delete();
		LabSectionApproverRelationShip::where('parent_id',$approver->id)->delete();
		$data = [];
		foreach($request->section_ids as $id){
			$data[]=[
				"lab_section_id"=>$id,
				"user_id"=>$request->user_id,
				"title"=>$request->title,
				"parent_id"=>$approver->id
			];
		}
		LabSectionApproverRelationShip::insert($data);
		return redirect()->back()->with('success', 'Lab section approver record(s) updated successfully');
	}
	public function deleteSectionApproval(Request $request){
		LabSectionApprover::find($request->approver_id)->delete();
		LabSectionApproverRelationShip::where('parent_id',$request->approver_id)->delete();
		
		return redirect()->back()->with('success', 'Lab section approver record(s) deleted successfully');
	}
}
