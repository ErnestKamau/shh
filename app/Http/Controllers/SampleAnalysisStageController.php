<?php

namespace App\Http\Controllers;

use App\Lab;
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

		return view('layouts.lab.sample-analysis-stages.index', compact('sampleAnalysisStage','users','labs'));
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
}
