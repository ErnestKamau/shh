<?php

namespace App\Http\Controllers;

use App\Lab;
use App\Company;
use App\SampleType;
use App\SampleAnalysisStage;
use App\Models\Lab\Sample\SampleTypeQualification;
use App\Models\Lab\Qualification;
use App\AnalysisType;
use App\AnalysisElements;
use App\SampleTypeCategory;
use Illuminate\Http\Request;

class SampleTypeController extends Controller
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

  public function index()
  {
    $sample_types = SampleType::join('companies as c', 'c.id', '=', 'sample_types.company_id')->selectRaw('sample_types.*, c.name as company')->get();
    $companies = Company::all();
    $categories = SampleTypeCategory::all();

    return view('layouts.lab.sample-types.index', compact('companies', 'sample_types','categories'));
  }

  public function show($id)
  {
    $sample_type = SampleType::find($id);

    $qualifications = SampleTypeQualification::where('sample_id',$id)->orderBy('is_mandatory','desc')->get();
    $qualification_list = Qualification::all();
    

    $analysis_types = SampleType::find($id)->analysis_types;

    $analysis_typesArr = array("inactive"=>array(), "active"=>array());

    foreach($analysis_types as $aType){
      $active = $aType->active == "0" ? "inactive" : "active";
      $analysis_typesArr[$active][] = $aType;
    }

    $labs = Lab::all();

    $sample_analysis_stage = SampleAnalysisStage::all();


    $analysis_types = $analysis_typesArr;

    return view('layouts.lab.sample-types.show', compact('analysis_types', 'labs', 'sample_type', 'sample_analysis_stage','qualifications','qualification_list'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function add(Request $request)
  {
    $sample_type = new SampleType;
    $sample_type->name = $request->name;
    $sample_type->code = $request->code;
    $sample_type->description = $request->description;
    $sample_type->company_id = getUserCompany();
    $sample_type->active = $request->active ?? 0;
    $sample_type->sample_type_category = $request->category_id;

    $sample_type->save();

    return redirect()->back()->with('success', 'Sample Type added.');
  }

  public function edit(Request $request, $id)
  {
    $sample_type = SampleType::find($id);
    $sample_type->name = $request->name;
    $sample_type->code = $request->code;
    $sample_type->description = $request->description;
    $sample_type->company_id = getUserCompany();
    $sample_type->active = $request->active ?? 0;
    $sample_type->sample_type_category = $request->category_id;


    $sample_type->save();

    return redirect()->back()->with('success', 'Sample Type edited.');
  }
  public function getLastLevel($sample_type_id){
		$maxLevel = AnalysisType::where('sample_type_id', $sample_type_id)->max('level');
		return $maxLevel ?? 0;
	}
  public function move_sample_types($direction, $sample, $element){
		$theElement = AnalysisType::find($element);
		$currentLevel = $theElement->level;
		$currentMaxLevel = $this->getLastLevel($sample);

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
		$sibling = AnalysisType::where('sample_type_id', $sample)->where('level', $newLevel)->first();

		if($sibling){
			$sibling->level = $currentLevel;
			$sibling->save();
		}

		$theElement->level = $newLevel;
		$theElement->save();

		return json_encode(array("status"=>true));
  }
  public function sort_analysis(){
    $sampletypes = SampleType::all();
    foreach($sampletypes as $type){
      $analysis = AnalysisType::where('sample_type_id',$type->id)->orderBY('id','asc')->get();
      $count = 1;
      foreach($analysis as $a){
        $a->level = $count;
        $a->save();
        ++$count;
      }

      $count = 1;
    }
    return response()->json('success');
  }
  public function delete_sample_type(Request $request){
    // return response()->json('test');
    $sample = SampleType::find($request->sample_type_id);
    if(!isset($sample->id)){
      return redirect()->back()->with('error','No sample type with the specified ID');
    }
    if($sample->analysis_types->count() > 0){
      return redirect()->back()->with('error',$sample->name .'Sample Type cannot be deleted since it has analysis types configured');
    }
    $sample->delete();
    return redirect()->back()->with('success','Sample Type deleted successfully!');
  }
}
