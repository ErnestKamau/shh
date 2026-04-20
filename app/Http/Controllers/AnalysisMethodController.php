<?php

namespace App\Http\Controllers;

use App\Analyte;
use App\Company;
use App\AnalysisMethod;
use App\MethodValidationRequest;

use App\Models\System\SystemConfiguration;
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
    $reference_id = SystemConfiguration::where('key','method_reference_id')->first();
    $ltm_id = SystemConfiguration::where('key','method_ltm_id')->first();
    $methods = AnalysisMethod::with(['referencemethod','methodtype'])->get();
    $method_types = SystemConfiguration::where('key','method_type')->get();

    $references = AnalysisMethod::where('method_type_id',$reference_id)->get();

    return view('layouts.lab.methods.index', compact('companies', 'methods','references','reference_id','method_types','ltm_id'));
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
    $analysis_type->method_type_id = $request->method_type_id;
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
    $analysis_type->method_type_id = $request->method_type_id;
    $analysis_type->save();

    return redirect()->back()->with('success', 'Analysis Method edited.');
  }

  public function show(Request $request, $id){
    $companies = Company::all();
    $analysis_method = AnalysisMethod::with(['referencemethod','methodtype'])->find($id);
    $analytes = Analyte::all();


    return view('layouts.lab.methods.show', compact('analysis_method', 'analytes', 'companies'));
  }

  public function sendForValidation(Request $request)
  {
    $request->validate([
      'method_id' => 'required|exists:analysis_methods,id',
      'lab_assigned' => 'nullable|exists:users,id',
      'sample_header_id' => 'nullable|exists:sample_headers,id',
      'notes' => 'nullable|string',
    ]);

    $method = AnalysisMethod::findOrFail($request->method_id);
    $method->validation_status = AnalysisMethod::STATUS_SENT_FOR_VALIDATION;

    if ($request->filled('sample_header_id')) {
      $method->sample_header_id = $request->sample_header_id;
    }

    $method->save();

    MethodValidationRequest::create([
      'method_id' => $method->id,
      'requested_by' => auth()->id(),
      'lab_assigned' => $request->lab_assigned,
      'status' => MethodValidationRequest::STATUS_PENDING,
      'validation_data' => $request->input('validation_data'),
      'notes' => $request->notes,
      'requested_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Method sent for validation successfully.');
  }
}
