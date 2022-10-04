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
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request)
	{
		$condition = new SampleCondition;
		$condition->name = $request->name;
		$condition->active = $request->active ?? 0;
		$condition->sample_type_id = $request->sample_type_id;
		$condition->save();

		return redirect()->back()->with('success', 'Sample Condition Added.');
	}

	public function edit(Request $request, $id)
	{
		$condition = SampleCondition::find($id);
		$condition->name = $request->name;
		$condition->active = $request->active ?? 0;
		$condition->sample_type_id = $request->sample_type_id;
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
