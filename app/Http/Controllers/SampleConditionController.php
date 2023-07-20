<?php

namespace App\Http\Controllers;

use App\SampleCondition;
use Illuminate\Http\Request;

class SampleConditionController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
  }
	
	public function index()
	{
		$conditions = SampleCondition::orderBy('active','DESC')->orderBy('name','ASC')->get();
		return view('layouts.lab.sample-condition.index',compact('conditions'));
	}

	
	public function add(Request $request)
	{
		$condition = new SampleCondition;
		$condition->name = $request->name;
		$condition->active = $request->active ?? 0;
		$condition->sample_type_id = isset($request->sample_type_id) ? $request->sample_type_id : 0;
		$condition->save();

		return redirect()->back()->with('success', 'Sample Condition Added.');
	}

	public function edit(Request $request)
	{
		$condition = SampleCondition::find($request->condition_id);
		$condition->name = $request->name;
		$condition->active = $request->active ?? 0;
		$condition->sample_type_id = isset($request->sample_type_id) ? $request->sample_type_id : 0;
		$condition->save();

		return redirect()->back()->with('success', 'Sample Condition Edited.');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\SampleCondition  $sampleCondition
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, SampleCondition $sampleCondition)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\SampleCondition  $sampleCondition
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(SampleCondition $sampleCondition)
	{
		//
	}
}
