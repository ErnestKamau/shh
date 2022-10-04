<?php

namespace App\Http\Controllers\Lab\Samples;

use App\SampleType;
use App\Models\Lab\Sample\SampleTypeQualification;

use App\Http\Controllers\Controller;
use App\Models\Lab\Qualification;
use Illuminate\Http\Request;

class SampleQualificationsController extends Controller
{

    public function __construct()
    {
       $this->middleware('auth') ;
    }

    public function add(Request $request,$id){
        $sample_type = SampleType::find($id);
        $qualification = Qualification::find($request->qualification);
        if(!isset($qualification->id)){
            return redirect()->back()->with('error','The qualification you choosed is not present!');
        }
        if(isset($sample_type->code)){
            $new_qualification = new SampleTypeQualification();
            $new_qualification->sample_id = $sample_type->id;
            $new_qualification->qualification_id = $request->qualification;
            if(isset($request->mandatory)){
                $new_qualification->is_mandatory = 1;
            }
            $new_qualification->save();
            
            return redirect()->back()->with('success','Sample qualification added successfully!');
        }else{
            return redirect()->back()->with('error','No sample with specified ID');
        }
    }

    public function edit(Request $request,$id){
        $qualification = SampleTypeQualification::find($id);
        $qualification_choosed = Qualification::find($request->qualification);
        if(!isset($qualification_choosed->id)){
            return redirect()->back()->with('error','The qualification you choosed is not present!');
        }
        if(isset($qualification->sample_id)){
            $qualification->qualification_id = $request->qualification;
            $qualification->edited_by = auth()->user()->name;
            if(isset($request->mandatory)){
                $qualification->is_mandatory = 1;
            }
            $qualification->save();

            return redirect()->back()->with('success','Sample qualification edited successfully! ');
        }else{
            return redirect()->back()->with('error','No sample qulification with the specified ID');
        }
    }

    public function delete(Request $request, $id){
        $qualification = SampleTypeQualification::find($id);
        if($qualification->id = $request->qualification){
            $qualification->edited_by = auth()->user()->name;
            $qualification->status = 1;
            $qualification->save();

            return redirect()->back()->with('success','Sample qualification deleted successfully!');
        }else{
            return redirect()->back()->with('error','No sample qualification with the specified ID!');
        }
    }

}
