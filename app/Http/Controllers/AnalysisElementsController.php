<?php

namespace App\Http\Controllers;

use App\Analyte;
use App\AnalysisElements;
use App\AnalysisMethod;
use App\AnalysisType;
use Illuminate\Http\Request;

class AnalysisElementsController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

	public function getLastLevel($analysis_type_id){
		$maxLevel = AnalysisElements::where('analysis_type_id', $analysis_type_id)->max('level');
		return $maxLevel ?? 0;
	}

  public function add(Request $request)
  {
		$exists = AnalysisElements::where('analyte_id', $request->analyte_id)
			->where('analysis_type_id', $request->analysis_type_id)->first();

		if($exists){
			return redirect()->back()->with('error', 'Analysis Element already exists.');
		}

    $analyte = Analyte::find($request->analyte_id);
    $element = new AnalysisElements;

    $element->analyte_id = $request->analyte_id;
    $element->decimal_places = $request->decimal_places;
    $element->reporting_symbol = $request->reporting_symbol;
    $element->reporting_unit = $request->reporting_unit;
    $element->method = $request->method;
    $element->equipment_id = $request->equipment_id;
    $element->operator_id = $request->operator_id;
    $element->significant_figures = $request->significant_figures;
    $element->lod = $request->lod;
    $element->hod = $request->hod;
    $element->level = $this->getLastLevel($request->analysis_type_id)+1;
    $element->non_detectable = $request->non_detectable ?? 0;
    $element->non_accredited = $request->non_accredited ?? 0;
    $element->active = $request->active ?? 0;
    $element->company_id = $analyte->company_id;
    $element->analysis_type_id = $request->analysis_type_id;
    $element->reporting_time = $request->report_time;
    $element->show_on_report = $request->show_on_report ?? 0;
		$element->is_manual = $request->is_manual ?? 0;
    $element->lab_section_id = AnalysisType::find($request->analysis_type_id)->lab_section_id;

		// return response()->json($element, 200);

    $element->save();

    return redirect()->back()->with('success', 'Analysis Element added.');
  }

  public function edit(Request $request, $id)
  {
    
    $analyte = Analyte::find($request->analyte_id);
    $element = AnalysisElements::find($id);

    $element->analyte_id = $request->analyte_id;
    $element->decimal_places = $request->decimal_places;
    $element->reporting_symbol = $request->reporting_symbol;
    $element->reporting_unit = $request->reporting_unit;
    $element->method = $request->method;
    $element->equipment_id = $request->equipment_id;
    $element->operator_id = $request->operator_id;
    $element->significant_figures = $request->significant_figures;
    $element->lod = $request->lod;
    $element->hod = $request->hod;
    $element->non_detectable = $request->non_detectable ?? 0;
    $element->non_accredited = $request->non_accredited ?? 0;
    $element->active = $request->active ?? 0;
    $element->company_id = $analyte->company_id;
    $element->analysis_type_id = $request->analysis_type_id;
    $element->reporting_time = $request->report_time;
    $element->show_on_report = $request->show_on_report ?? 0;
    $element->is_manual = $request->is_manual ?? 0;
    $element->lab_section_id = AnalysisType::find($request->analysis_type_id)->lab_section_id;

    $element->save();

    return redirect()->back()->with('success', 'Analysis Element edited.');
	}

	public function move_analysis_analyte($direction, $analysis, $element){
		$theElement = AnalysisElements::find($element);
		$currentLevel = $theElement->level;
		$currentMaxLevel = $this->getLastLevel($analysis);

		if($currentMaxLevel == 0 || $currentLevel == null){
			$theElement->level = $currentMaxLevel+1;
			$theElement->save();

			return json_encode(array("status"=>true));
		}

		if($direction == 'move-up'){
			$newLevel = intval($currentLevel)-1;
		}
		else{
			$newLevel = intval($currentLevel)+1;
		}

		$newLevel = $newLevel < 1 ? 1 : $newLevel;
		$sibling = AnalysisElements::where('analysis_type_id', $analysis)->where('level', $newLevel)->first();

		if($sibling){
			$sibling->level = $currentLevel;
			$sibling->save();
		}

		$theElement->level = $newLevel;
		$theElement->save();

		return json_encode(array("status"=>true));
	}
  public function getAnalyteMethods($id){
    $analyte = getAnalyteByID($id);
    $methods = $analyte->methods();
    $all_methods = AnalysisMethod::all()->pluck('id','name')->toArray();
    return response()->json(sizeof($methods) > 0 ? $methods : $all_methods);
  }
}
