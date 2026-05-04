<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Chain_of_Custody_Complaint;
Use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintsresolutions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintWorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function approve_next(Request $request,$id){
        $complaint  = Complaint::find($id);
        $current_stage = $complaint->complaint_workflow;
        $workflow_stages = getComplaintsWorkFlowValues();
        foreach ($workflow_stages as $x=>$x_value){
            if($x_value == $complaint->complaint_workflow){
                $current_workflow = $x;
                $next_stage_value =$x_value + 1;
            }
        }
        if($next_stage_value == 5){
            $complaint->edited_by = auth()->user()->name;
        }
        
        $complaint->complaint_workflow = $next_stage_value;
        $complaint->save();

        $chain_custody = new Chain_of_Custody_Complaint();
        $approval_actions = getComplaintsActionsApproval();
        foreach($approval_actions as $a=>$a_value){
            if($a == $current_stage){
                $action = $a_value;
            }
        }


        $chain_custody->complaint_id = $complaint->id;
        $chain_custody->action = $action;  
        $chain_custody->action_taker_id = auth()->user()->id;
        $chain_custody->workflow_stage = $current_stage;
        $chain_custody->comments = $request->comment;
        $chain_custody->move_out_date = getTodayDate();

        $chain_custody->save();

        return redirect()->route('crm.complaints-manager',['stage'=>$current_workflow])->with('sucess','Complaint approved successfully');
    }
    public function reverse_approval(Request $request,$id){
        $complaint = Complaint::find($id);
        $current_stage = $complaint->complaint_workflow;
        $workflow_stages = getComplaintsWorkFlowValues();
        foreach($workflow_stages as $x=>$x_value){
            if($current_stage == $x_value){
                $current_workflow = $x;
            }
        }
        $complaint->complaint_workflow = $current_stage -1;
        $complaint->save();

        $new_custody = new Chain_of_Custody_Complaint();

        $reverse_actions = getComplaintActionReverse();
        foreach($reverse_actions as $r=>$r_value){
            if($r == $current_stage){
                $action = $r_value;
            }
        }

        $new_custody->complaint_id = $complaint->id;
        $new_custody->action = $action;
        $new_custody->action_taker_id = auth()->user()->id;
        $new_custody->workflow_stage = $current_stage;
        $new_custody->comments = $request->comment;
        $new_custody->move_out_date = getTodayDate();

        $new_custody->save();

        return redirect()->route('crm.complaints-manager',['stage'=>$current_workflow])->with('sucess','Complaint reversed successfully');
    }
    public function reject_complaint(Request $request,$id){
        $complaint = Complaint::find($id);
        $current_stage = $complaint->complaint_workflow;
        $workflow_stages = getComplaintsWorkFlowValues();
        foreach($workflow_stages as $x=>$x_value){
            if($current_stage == $x_value){
                $current_workflow = $x;
            }
        }
        $complaint->rejected = 1;
        $complaint->complaint_workflow = 6;
        $complaint->reject_workflow = $current_stage;
        $complaint->save();

        $new_chain = new Chain_of_Custody_Complaint();
        $new_chain->complaint_id = $complaint->id;
        $new_chain->action = "Reject Complaint";
        $new_chain->action_taker_id = auth()->user()->id;
        $new_chain->workflow_stage = $complaint->complaint_workflow;
        $new_chain->comments = $request->comment;
        $new_chain->move_out_date = getTodayDate();

        $new_chain->save();

        return redirect()->route('crm.complaints-manager',['stage'=>$current_workflow])->with('sucess','Complaint rejected successfully');
    }
   
    
    public function reverse_resolution(Request $request,$id){
        $resolution = Complaintsresolutions::find($id);
        if(isset($resolution->complaint_id)){
            $complaint = Complaint::find($resolution->complaint_id);
            $current_stage = $complaint->complaint_workflow;
            $workflow_stages = getComplaintsWorkFlowValues();
            foreach($workflow_stages as $x=>$x_value){
                if($current_stage == $x_value){
                    $current_workflow = $x;
                }
            }
            $resolution->request_approve = 0;
            $resolution->workflow_stage = $current_stage - 1;
            $resolution->save();

            $complaint->complaint_workflow = $current_stage -1;
            $complaint->save();

            $new_custody = new Chain_of_Custody_Complaint();
            $new_custody->complaint_id = $complaint->id;
            $new_custody->action = "Reverse Complaint Resolutions ";
            $new_custody->action_taker_id = auth()->user()->id;
            $new_custody->workstage_stage = $current_stage;
            $new_custody->comments = $request->comment;
            $new_custody->moved_out_date = getTodayDate();

            $new_custody->save();

            return redirect()->route('crm.complaints-manager',['stage'=>$current_workflow])->with('sucess','Complaint resolution reversed successfully');
        }else{
            return redirect()->back()->with('error','No resolution with specified ID!');
        }
    }
}