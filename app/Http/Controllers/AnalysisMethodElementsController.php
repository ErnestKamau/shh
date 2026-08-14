<?php

namespace App\Http\Controllers;

use App\AnalysisGuide;
use App\StandardValue;
use App\AnalysisMethodElements;
use App\StandardAnalytes;
use Illuminate\Http\Request;

class AnalysisMethodElementsController extends Controller
{
	public function __construct()
  {
    $this->middleware('auth');
	}

  public function add(Request $request)
  {

    $element = new AnalysisMethodElements;

    $element->analysis_method_id = $request->analysis_method_id;
    $element->analyte_id = $request->analyte_id;
    $element->quantity = $request->quantity;
    $element->company_id = getUserCompany();
    $element->active = $request->boolean('active', true) ? 1 : 0;

    $element->save();

    return redirect()->back()->with('success', 'Analysis Method Element added.');
  }

  public function edit(Request $request, $id)
  {

    $element = AnalysisMethodElements::find($id);

    $element->analysis_method_id = $request->analysis_method_id;
    $element->analyte_id = $request->analyte_id;
    $element->quantity = $request->quantity;
    $element->company_id = getUserCompany();
    $element->active = $request->active ?? 0;

    $element->save();

    return redirect()->back()->with('success', 'Analysis Method Element edited.');
	}

	public function update_guide(Request $request){
		$guide = StandardAnalytes::where('id',$request->guide_id)->first() ?? new StandardAnalytes();

    
    $guide->analyte_id = $request->analyte_id;
    
    
    $guide->comments = $request->comments;
    $guide->recommendations = $request->recommendations;
    $guide->standard_id=$request->standard_id;
    $guide->standard_value_type =$request->standard_value_type;
    $guide->value_type = '';
    if(isset($request->standard_value_type) && $request->standard_value_type == 'is_range'){

      $guide->high = $request->high_range;

      $guide->low =$request->low_range ;
      $guide->standard_value_id = 0;
      $guide->standard_is_value='';
    }elseif(isset($request->standard_value_type) && $request->standard_value_type == 'is_standard_value'){
      $standard = StandardValue::where('code',$request->standard_value)->get();
      
      $guide->standard_value_id = $standard[0]->id;
      $guide->standard_is_value='';
      if(isset($standard[0]->id) && $standard[0]->name == 'Is Value'){

        $guide->standard_is_value= $request->is_value;
        $guide->value_type = $request->limit_measure;
      }
      $guide->high = '';

      $guide->low ='';

    }
    $guide->save();

    return redirect()->back()->with('success', 'The guide has been added.');
  }
  public function clone_analysis_guide(Request $request){
    $guide = StandardAnalytes::find($request->guide_id);
    if(!isset($guide->id)){
      return redirect()->back()->with('error','No A with specified ID!');
    }
    $new_guide = new StandardAnalytes();
     
    $new_guide->analyte_id=$guide->analyte_id;
    
    $new_guide->comments = $guide->comments;
    $new_guide->recommendations =  $guide->recommendations;
    $new_guide->standard_id = $guide->standard_id;
    $new_guide->standard_value_type = $guide->standard_value_type ;
    $new_guide->high = $guide->high;
    $new_guide->low =  $guide->low;
    $new_guide->standard_value_id = $guide->standard_value_id;
    $new_guide->standard_is_value = $guide->standard_is_value;
    $new_guide->value_type=$guide->value_type;
    $new_guide->save();
    return redirect()->back()->with('success','Analyte Standard cloned successfully!');
  }
  public function delete_analysis_guide(Request $request){
    $guide = StandardAnalytes::find($request->guide_id);
    if(!isset($guide->id)){
      return redirect()->back()->with('error','Analyte Standard record is already deleted!');
    }else{
      $guide->delete();
      return redirect()->back()->with('success','Analysis guide deleted successfully!');
    }

  }
}
