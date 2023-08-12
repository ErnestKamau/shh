<?php

namespace App\Http\Controllers\Lab;

use App\Standards;
use App\StandardValue;
use App\Analyte;
use App\StandardAnalytes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpParser\PrettyPrinter\Standard;

class StandardsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function addStandard(Request $request){
        $standard = new Standards();
        $standards = Standards::all();
        $standard->code = $request->code;
        $standard->name = $request->name;
				$standard->status = $request->active ?? 0;

        if(isset($request->main_standard)){
            foreach($standards as $st){
                $st->main_standard = 0;
                $st->save();
            }
            $standard->main_standard = 1;
        }
        $standard->save();
        return redirect()->back()->with('success','Standard added successfully!');
    }
    public function editStandard(Request $request,$id){
        $standard = Standards::find($id);
        $standards = Standards::all();
        if(!isset($standard->id)){
            return redirect()->back()->with('error','No standard with the specified ID');
        }else{
            $standard->code = $request->code;
            $standard->name = $request->name;
            $standard->edited_by = auth()->user()->id;
						$standard->status = $request->active ?? 0;

            if(isset($request->main_standard)){
                foreach($standards as $st){
                    $st->main_standard = 0;
                    $st->save();
                }
                $standard->main_standard = 1;

            }elseif(!isset($request->main_standard) && $standard->main_standard == 1){
                $standard->main_standard = 0;
            }
            $standard->save();
            return redirect()->back()->with('success','Standard edited successfully');
        }
    }
    public function addStandardValues(Request $request){
        $standard_value = new StandardValue();
        $standard_value->code = $request->code;
        $standard_value->name = $request->name;
				$standard_value->status = $request->active ?? 0;

        $standard_value->save();
        return redirect()->back()->with('success','Standard Value added successfully!');
    }
    public function editStandardValue(Request $request,$id){
        $standard_value = StandardValue::find($id);
        if(!isset($standard_value->id)){
            return redirect()->back()->with('error','No standard value with specified id');
        }else{
            $standard_value->code = $request->code;
            $standard_value->name = $request->name;
            $standard_value->edited_by = auth()->user()->id;
			$standard_value->status = $request->active ?? 0;
            // return response()->json($standard_value,200);
            $standard_value->save();
            return redirect()->back()->with('success','Standard Value added successfully!');
        }

    }
    public function show($id){
        $standard = Standards::find($id);
        if(isset($standard->id)){
            $analytes = Analyte::all();
            $standard_analyte = StandardAnalytes::where('standard_id',$id)->get();
            foreach($standard_analyte as $a){
                $analyte = Analyte::find($a->analyte_id);
                $standard_value = StandardValue::find($a->standard_value_id);
                $a->analyte_code = $analyte->code ?? '';
                $a->analyte_name = $analyte->name ?? '';
                if(isset($standard_value->id)){

                    $a->standard_value_name = $standard_value->name;
                    $a->standard_value_code = $standard_value->code;
                }
            }
            $standard_values = StandardValue::where('status',1)->get();
            return view('layouts.lab.sample-types.show_standard',compact('analytes','standard','standard_analyte','standard_values'));
        }else{
            return redirect()->back()->with('error','No standard with specified ID!');
        }
    }
}
