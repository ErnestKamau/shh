<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintsresolutions;
use App\Services\CRM\ComplaintInvestigationReportService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComplaintWorkflowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function approve_next(Request $request,$id){
        $complaint  = Complaint::findOrFail($id);
        $current_stage = (int) $complaint->complaint_workflow;
        $current_workflow = getComplaintWorkflow()[$current_stage] ?? 'All Complaints';
        $next_stage_value = getComplaintWorkflowNextStageMap()[$current_stage] ?? null;

        if ($next_stage_value === null) {
            return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('error','Complaint cannot be approved from the current workflow stage.');
        }

        $approval_actions = getComplaintsActionsApproval();
        foreach($approval_actions as $a=>$a_value){
            if($a == $current_stage){
                $action = $a_value;
            }
        }

        try {
            DB::transaction(function () use ($complaint, $current_stage, $next_stage_value, $request, $action) {
                if($next_stage_value == 5){
                    $complaint->edited_by = auth()->user()->name;
                    $complaint->is_closed = 1;
                    $complaint->closed_by = auth()->id();
                    $complaint->date_closed = now();
                }

                $complaint->complaint_workflow = $next_stage_value;
                $complaint->save();

                $chain_custody = new Chain_of_Custody_Complaint();
                $chain_custody->complaint_id = $complaint->id;
                $chain_custody->action = $action;
                $chain_custody->action_taker_id = auth()->user()->id;
                $chain_custody->workflow_stage = getComplaintWorkflow()[$current_stage] ?? $current_stage;
                $chain_custody->comments = $request->comment;
                $chain_custody->move_out_date = getTodayDate();
                $chain_custody->save();

                if ($next_stage_value == 5) {
                    $complaint->refresh();

                    app(ComplaintInvestigationReportService::class)
                        ->generateAndAttachCloseReports($complaint);
                }
            });
        } catch (\Throwable $e) {
            return redirect()->route('complaint-workflow', ['stage' => $current_workflow])
                ->with('error', $e->getMessage());
        }

        return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint approved successfully');
    }
    public function reverse_approval(Request $request,$id){
        $complaint = Complaint::findOrFail($id);
        $current_stage = (int) $complaint->complaint_workflow;
        $current_workflow = getComplaintWorkflow()[$current_stage] ?? 'All Complaints';
        $previous_stage = getComplaintWorkflowPreviousStageMap()[$current_stage] ?? null;

        if ($previous_stage === null) {
            return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('error','Complaint cannot be reversed from the current workflow stage.');
        }

        $complaint->complaint_workflow = $previous_stage;
        if ($current_stage === 5) {
            $complaint->is_closed = 0;
            $complaint->closed_by = null;
            $complaint->date_closed = null;
        }
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
        $new_custody->workflow_stage = getComplaintWorkflow()[$current_stage] ?? $current_stage;
        $new_custody->comments = $request->comment;
        $new_custody->move_out_date = getTodayDate();

        $new_custody->save();

        return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint reversed successfully');
    }
    public function reject_complaint(Request $request,$id){
        $complaint = Complaint::findOrFail($id);
        $current_stage = (int) $complaint->complaint_workflow;
        $current_workflow = getComplaintWorkflow()[$current_stage] ?? 'All Complaints';
        $complaint->rejected = 1;
        $complaint->complaint_workflow = 6;
        $complaint->reject_workflow = $current_stage;
        $complaint->is_closed = 0;
        $complaint->closed_by = null;
        $complaint->date_closed = null;
        $complaint->save();

        $new_chain = new Chain_of_Custody_Complaint();
        $new_chain->complaint_id = $complaint->id;
        $new_chain->action = "Reject Complaint";
        $new_chain->action_taker_id = auth()->user()->id;
        $new_chain->workflow_stage = getComplaintWorkflow()[$complaint->complaint_workflow] ?? $complaint->complaint_workflow;
        $new_chain->comments = $request->comment;
        $new_chain->move_out_date = getTodayDate();

        $new_chain->save();

        return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint rejected successfully');
    }
    
    
    public function reverse_resolution(Request $request,$id){
        $resolution = Complaintsresolutions::find($id);
        if(isset($resolution->complaint_id)){
            $complaint = Complaint::findOrFail($resolution->complaint_id);
            $current_stage = (int) $complaint->complaint_workflow;
            $current_workflow = getComplaintWorkflow()[$current_stage] ?? 'All Complaints';
            $previous_stage = getComplaintWorkflowPreviousStageMap()[$current_stage] ?? null;

            if ($previous_stage === null) {
                return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('error','Complaint resolution cannot be reversed from the current workflow stage.');
            }

            $resolution->request_approve = 0;
            $resolution->workflow_stage = $previous_stage;
            $resolution->save();

            $complaint->complaint_workflow = $previous_stage;
            if ($current_stage === 5) {
                $complaint->is_closed = 0;
                $complaint->closed_by = null;
                $complaint->date_closed = null;
            }
            $complaint->save();

            $new_custody = new Chain_of_Custody_Complaint();
            $new_custody->complaint_id = $complaint->id;
            $new_custody->action = "Reverse Complaint Resolutions ";
            $new_custody->action_taker_id = auth()->user()->id;
            $new_custody->workflow_stage = getComplaintWorkflow()[$current_stage] ?? $current_stage;
            $new_custody->comments = $request->comment;
            $new_custody->move_out_date = getTodayDate();

            $new_custody->save();

            return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint resolution reversed successfully');
        }else{
            return redirect()->back()->with('error','No resolution with specified ID!');
        }
    }
}
