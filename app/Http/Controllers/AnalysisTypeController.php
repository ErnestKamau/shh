<?php

namespace App\Http\Controllers;

use App\AnalysisElements;
use App\Lab;
use App\Analyte;
use App\Result;
use App\SampleType;
use App\AnalysisType;
use App\CapturedResult;
use Illuminate\Http\Request;

class AnalysisTypeController extends Controller
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

  public function index(Request $request, $id = 0)
  {
    $selected_lab = Lab::find($id);

    $analysis_types = $selected_lab ? Lab::find($id)->analysis_types : AnalysisType::all();

    $sample_types = SampleType::all();
    $labs = Lab::all();

    return view('layouts.lab.analysis-types.index', compact('analysis_types', 'sample_types', 'labs', 'selected_lab'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */

  public function getLastLevel($analysis_type_id){
		$maxLevel = AnalysisType::where('sample_type_id', $analysis_type_id)->max('level');
		return $maxLevel ?? 0;
	}
  public function add(Request $request)
  {
    $lab = Lab::find($request->lab_id);

    $analysis_type = new AnalysisType;
    $analysis_type->name = $request->name;
    $analysis_type->short_name = $request->short_name;
    $analysis_type->code = $request->code;
    $analysis_type->description = $request->description;
    $analysis_type->company_id = $lab->company->id;
    $analysis_type->lab_id = $request->lab_id;
    $analysis_type->reporting_time = $request->reporting_time;
    $analysis_type->sample_type_id = $request->sample_type_id;
    $analysis_type->active = $request->active ?? 0;
    $analysis_type->level = $this->getLastLevel($request->sample_type_id);
    $analysis_type->lab_section_id = $request->lab_section_id;

    $analysis_type->save();
    
    AnalysisElements::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);
    CapturedResult::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);
    Result::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);

    return redirect()->back()->with('success', 'Analysis Type added.');
  }

  public function edit(Request $request, $id)
  {
    $lab = Lab::find($request->lab_id);

    $analysis_type = AnalysisType::find($id);
    $analysis_type->name = $request->name;
    $analysis_type->short_name = $request->short_name;
    $analysis_type->code = $request->code;
    $analysis_type->description = $request->description;
    $analysis_type->company_id = $lab->company->id;
    $analysis_type->lab_id = $request->lab_id;
    $analysis_type->reporting_time = $request->reporting_time;
    $analysis_type->sample_type_id = $request->sample_type_id;
    $analysis_type->active = $request->active ?? 0;
    $analysis_type->lab_section_id = $request->lab_section_id;
    $analysis_type->save();
    AnalysisElements::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);
    CapturedResult::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);
    Result::where('analysis_type_id',$analysis_type->id)->update(['lab_section_id'=>$analysis_type->lab_section_id]);

    return redirect()->back()->with('success', 'Analysis Type edited.');
  }

  public function show(Request $request, $id){
    $analytes = Analyte::all();
    $analysis_type = AnalysisType::find($id);
    $sample_types = SampleType::all();
    $labs = Lab::all();

    $analysis_type_id = $id;
    // return response()->json($analysis_type->guides,200);
    // foreach($analysis_type->guides as $ag){
    //   $analysis_standard = getStandardByid($ag->standard_id);
    // }

    return view('layouts.lab.analysis-types.show', compact('analysis_type', 'sample_types', 'labs', 'analytes', 'analysis_type_id'));
	}

	public function by_sample_id($id){
		$analysis_types = AnalysisType::where('sample_type_id', $id)->get();

		return response()->json($analysis_types, 200);
	}
}
