<?php

namespace App\Http\Controllers;

use App\SampleToSampleAnalysisStage;
use Illuminate\Http\Request;

class SampleToSampleAnalysisStageController extends Controller
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
	public function add(Request $request, $id)
	{
		$stage = new SampleToSampleAnalysisStage;
		$stage->sample_type_id = $id;
		$stage->sample_analysis_stage_id = $request->sample_analysis_stage_id;
    $stage->active = $request->active ?? 0;
		$stage->save();

    return redirect()->back()->with('success', 'Sample Analysis Stage Added.');
	}

	public function update(Request $request, $id)
	{
		$stage = SampleToSampleAnalysisStage::find($id);
    $stage->active = $request->active ?? 0;
		$stage->save();

    return redirect()->back()->with('success', 'Sample Analysis Stage Edited.');
	}
}
