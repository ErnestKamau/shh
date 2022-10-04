<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Complaintsresolutions;

use App\Http\Controllers\Controller;

use App\Models\CRM\Complaint;
use Illuminate\Http\Request;

class ComplaintResolutionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function add(Request $request,$id){
        $new_resolution = new Complaintsresolutions();
        $complaint = Complaint::find($id);
        if (isset($complaint->complaint_id)){
            $resolutions = Complaintsresolutions::where('complaint_id',$complaint->id)->get();
            $resolutions_count = count($resolutions) + 1;
            $res_countstr = strval($resolutions_count);
            if(strlen($res_countstr)<4){
                $diff = 4-strlen($res_countstr);
                $zero = str_repeat("0",$diff);
                $new_resolution->car_no = $complaint->complaint_id."RES".$zero.$res_countstr;
            }else{
                $new_resolution->car_no = $complaint->complaint_id."RES".$res_countstr;
            }


            $new_resolution->action = $request->action;
            $new_resolution->officer_responsible = $request->officer_responsible;
            $new_resolution->registered_by = auth()->user()->name;
            $new_resolution->complaint_id = $complaint->id;
            $new_resolution->workflow_stage = $complaint->complaint_workflow;

            $new_resolution->save();

            return redirect()->back()->with('success','Resolution added successfully!');       

        }else{
            return redirect()->back()->with('error','No complaint with specified ID');
        }
    }

    public function edit(Request $request,$id){
        $resolution = Complaintsresolutions::find($id);
        if (isset($resolution->car_no)){
            $resolution->action = $request->action;
            $resolution->officer_responsible = $request->officer_responsible;
            $resolution->reject  = $request->status;
            $resolution->edited_by = auth()->user()->name;

            $resolution->save();

            return redirect()->back()->with('success','resolution edited successfully');
        }else{
            return redirect()->back()->with('error','No resolution with the specified ID!');
        }
    }
    
}
