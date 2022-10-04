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
	public function index()
	{
		$reporting_units = ReportingUnit::orderBy('name', 'asc')->get();

		return view('layouts.lab.reporting-units.index', compact('reporting_units'));
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
