<?php

namespace App\Http\Controllers;

use Auth;
use App\Company;
use App\Analyte;
use Illuminate\Http\Request;

class AnalyteController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $companies = Company::all();
		$analytes = Analyte::where('company_id', getUserCompany())->get();

		// return response()->json(getUserCompany(), 200);

    return view('layouts.lab.analytes.index', compact('companies', 'analytes'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function add(Request $request)
  {
    $analyte = new Analyte;

    $analyte->code = $request->code;
    $analyte->name = $request->name;
    $analyte->common_name = $request->common_name;
    $analyte->decimal_places = $request->decimal_places;
    $analyte->reporting_symbol = $request->reporting_symbol;
    $analyte->equivalent_weight = $request->equivalent_weight;
    $analyte->reporting_unit = $request->reporting_unit;
    $analyte->method = implode(",", $request->method ?? []);
    $analyte->equipment_id = implode(",", $request->equipment_id ?? []);
    $analyte->non_detectable = $request->non_detectable ?? 0;
    $analyte->non_accredited = $request->non_accredited ?? 0;
    $analyte->active = $request->boolean('active', true) ? 1 : 0;
    $analyte->company_id = getUserCompany();
    $analyte->show_on_report = $request->show_on_report ?? 0;
    $analyte->is_italic  = $request->is_italic ?? 0;
    $analyte->save();

    return redirect()->back()->with('success', 'Analyte added.');
  }

  public function edit(Request $request, $id)
  {

		// return response()->json($request->all(), 200);

    $analyte = Analyte::find($id);

    $analyte->code = $request->code;
    $analyte->name = $request->name;
    $analyte->common_name = $request->common_name;
    $analyte->decimal_places = $request->decimal_places;
    $analyte->reporting_symbol = $request->reporting_symbol;
    $analyte->reporting_unit = $request->reporting_unit;
    $analyte->equivalent_weight = $request->equivalent_weight;
    $analyte->method = implode(",", $request->method ?? []);
    $analyte->equipment_id = implode(",", $request->equipment_id ?? []);
    $analyte->non_detectable = $request->non_detectable ?? 0;
    $analyte->non_accredited = $request->non_accredited ?? 0;
    $analyte->active = $request->active ?? 0;
    $analyte->show_on_report = $request->show_on_report ?? 0;
    $analyte->is_italic  = $request->is_italic ?? 0;

    $analyte->save();

    return redirect()->back()->with('success', 'Analyte edited.');
  }
}
