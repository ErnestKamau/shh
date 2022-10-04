<?php

namespace App\Http\Controllers\Lab;

use App\Models\Lab\Qualification;

use App\Http\Controllers\Controller;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QualificationsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function add(Request $request){
        $new_qualification = new Qualification();

        $name = $request->name;
        $exist = Qualification::where('name',$name)->get();
        if (isset($exist->description)){
            return redirect()->back()->with('error','Theres is certification with the specified name');
        }else{
            if(isset($request->module)){
                $new_qualification->module_code = $request->module;
            }
            $new_qualification->name = $name;
            $new_qualification->description = $request->description;
            $new_qualification->save();
            return redirect()->back()->with('success','Certification added successfully!');
           
        }
    }

    public function edit(Request $request,$id){
        $qualification = Qualification::find($id);
        if($qualification->name == $request->current){
            $qualification->name = $request->name;
            $qualification->description = $request->description;
            $qualification->status = $request->status;
            $qualification->edited_by = auth()->user()->name;
            
            $qualification->save();
            
            return redirect()->back()->with('success','Certification edited successfully!');
        }else{
            return redirect()->back()->with('error','Your request ID does not match the edited qualiification!');
        }

    }

    public function index(){
        $qualifications = Qualification::all()->sortBy('name');

        return view('layouts.lab.qualifications.index',compact('qualifications'));
    }

}
