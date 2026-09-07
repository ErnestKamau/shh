<?php

namespace App\Http\Controllers;

use App\ReportingUnit;
use Illuminate\Http\Request;

class ReportingUnitController extends Controller
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
	public function index($module = null)
	{
		$reporting_units = ReportingUnit::orderBy('name', 'asc')->get();

		return view('layouts.lab.reporting-units.index', compact('reporting_units', 'module'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add(Request $request)
	{
		$reporting_unit = new ReportingUnit;

		$reporting_unit->name = $request->name;
		$reporting_unit->active = $request->active ?? 0;
		$reporting_unit->save();

		return redirect()->back()->with('success', 'Reporting Unit Added.');
	}
	public function addAjax(Request $request)
	{
		$reporting_unit = new ReportingUnit;

		$reporting_unit->name = $request->r_value;
		$reporting_unit->active = 1;
		$reporting_unit->save();

		return response()->json($reporting_unit);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\ReportingUnit  $reportingUnit
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$reporting_unit = ReportingUnit::find($id);

		$reporting_unit->name = $request->name;
		$reporting_unit->active = $request->active ?? 0;
		$reporting_unit->save();

		return redirect()->back()->with('success', 'Reporting Unit Edit.');
	}
}
