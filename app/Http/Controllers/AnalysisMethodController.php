<?php

namespace App\Http\Controllers;

use App\Analyte;
use App\Company;
use App\AnalysisMethod;
use Illuminate\Http\Request;

class AnalysisMethodController extends Controller
{
    /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index(Request $request, $sample_type_id = 0)
  {
    $companies = Company::all();

    $methods = AnalysisMethod::with(['referencemethod'])->get();
    $references = AnalysisMethod::where('is_sampling_method',0)->where('is_ltm',0)->get();

    return view('layouts.lab.methods.index', compact('companies', 'methods','references'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function add(Request $request)
  {

    $analysis_type = new AnalysisMethod;
    $analysis_type->name = $request->name;
    $analysis_type->code = $request->code;
    $analysis_type->description = $request->description;
    $analysis_type->company_id = getUserCompany();
    $analysis_type->active = $request->active ?? 0;
    $analysis_type->is_sampling_method = $request->method_type_id == 2 ? 1 : 0;
    $analysis_type->is_ltm = $request->method_type_id == 1 ? 1 : 0;
    $analysis_type->reference_type_id = $request->reference_method_id;
    $analysis_type->save();

    return redirect()->back()->with('success', 'Analysis Method added.');
  }

  public function edit(Request $request)
  {

    $analysis_type = AnalysisMethod::find($request->method_id);
    $analysis_type->name = $request->name;
    $analysis_type->code = $request->code;
    $analysis_type->description = $request->description;
    $analysis_type->company_id = getUserCompany();
    $analysis_type->active = $request->active ?? 0;
    $analysis_type->is_sampling_method = $request->method_type_id == 2 ? 1 : 0;
    $analysis_type->is_ltm = $request->method_type_id == 1  ?? 0;
    $analysis_type->reference_type_id = $request->reference_method_id;
    $analysis_type->save();

    return redirect()->back()->with('success', 'Analysis Method edited.');
  }

  public function show(Request $request, $id){
    $companies = Company::all();
    $analysis_method = AnalysisMethod::find($id);
    $analytes = Analyte::all();


    return view('layouts.lab.methods.show', compact('analysis_method', 'analytes', 'companies'));
  }
}
