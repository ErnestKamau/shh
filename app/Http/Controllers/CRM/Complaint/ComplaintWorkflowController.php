<?php

namespace App\Http\Controllers\CRM\Complaint;

use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaintsresolutions;
use App\Services\CRM\ComplaintWorkflowService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintWorkflowController extends Controller
{
    public function __construct(private readonly ComplaintWorkflowService $workflowService)
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

        try {
            $this->workflowService->advance($complaint, $request->comment, (string) auth()->id());
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

        $this->workflowService->reverse($complaint, $request->comment, (string) auth()->id());

        return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint reversed successfully');
    }
    public function reject_complaint(Request $request,$id){
        $complaint = Complaint::findOrFail($id);
        $current_stage = (int) $complaint->complaint_workflow;
        $current_workflow = getComplaintWorkflow()[$current_stage] ?? 'All Complaints';
        $this->workflowService->reject($complaint, $request->comment, (string) auth()->id());

        return redirect()->route('complaint-workflow',['stage'=>$current_workflow])->with('sucess','Complaint rejected successfully');
    }

    public function request_resolution_approve(Request $request, $id)
    {
        $complaint = Complaint::findOrFail($id);
        $this->workflowService->recordChainOfCustody(
            complaintId: (string) $complaint->id,
            action: 'Request Resolution Approval',
            actionTakerId: (string) auth()->id(),
            workflowStage: getComplaintWorkflow()[(int) $complaint->complaint_workflow] ?? (string) $complaint->complaint_workflow,
            comments: $request->comment
        );

        return redirect()->back()->with('sucess', 'Resolution approval requested successfully');
    }

    public function reject_resolution(Request $request, $id)
    {
        return $this->reverse_approval($request, $id);
    }

    public function approve_resolution(Request $request, $id)
    {
        return $this->approve_next($request, $id);
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
