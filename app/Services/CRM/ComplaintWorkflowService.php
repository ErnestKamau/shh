<?php

namespace App\Services\CRM;

use App\Jobs\GenerateCloseReports;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\CRM\Complaint;
use Illuminate\Support\Facades\DB;

class ComplaintWorkflowService
{
    public function advance(Complaint $complaint, ?string $comment, ?string $actionTakerId): Complaint
    {
        $currentStage = (int) $complaint->complaint_workflow;
        $nextStage = getComplaintWorkflowNextStageMap()[$currentStage] ?? null;

        if ($nextStage === null) {
            throw new \RuntimeException('Complaint cannot be approved from the current workflow stage.');
        }

        $action = getComplaintsActionsApproval()[$currentStage] ?? 'Workflow Advanced';

        return DB::transaction(function () use ($complaint, $currentStage, $nextStage, $comment, $action, $actionTakerId): Complaint {
            if ($nextStage === 5) {
                $complaint->edited_by = auth()->user()?->name ?? $complaint->edited_by;
                $complaint->is_closed = 1;
                $complaint->closed_by = $actionTakerId;
                $complaint->date_closed = now();
            }

            $complaint->complaint_workflow = $nextStage;
            $complaint->save();

            $this->recordChainOfCustody(
                complaintId: (string) $complaint->id,
                action: $action,
                actionTakerId: $actionTakerId,
                workflowStage: getComplaintWorkflow()[$currentStage] ?? (string) $currentStage,
                comments: $comment
            );

            if ($nextStage === 5) {
                GenerateCloseReports::dispatch((string) $complaint->id);
            }

            return $complaint->fresh();
        });
    }

    public function reverse(Complaint $complaint, ?string $comment, ?string $actionTakerId): Complaint
    {
        $currentStage = (int) $complaint->complaint_workflow;
        $previousStage = getComplaintWorkflowPreviousStageMap()[$currentStage] ?? null;

        if ($previousStage === null) {
            throw new \RuntimeException('Complaint cannot be reversed from the current workflow stage.');
        }

        return DB::transaction(function () use ($complaint, $currentStage, $previousStage, $comment, $actionTakerId): Complaint {
            $complaint->complaint_workflow = $previousStage;

            if ($currentStage === 5) {
                $complaint->is_closed = 0;
                $complaint->closed_by = null;
                $complaint->date_closed = null;
            }

            $complaint->save();

            $action = getComplaintActionReverse()[$currentStage] ?? 'Workflow Reversed';
            $this->recordChainOfCustody(
                complaintId: (string) $complaint->id,
                action: $action,
                actionTakerId: $actionTakerId,
                workflowStage: getComplaintWorkflow()[$currentStage] ?? (string) $currentStage,
                comments: $comment
            );

            return $complaint->fresh();
        });
    }

    public function reject(Complaint $complaint, ?string $comment, ?string $actionTakerId): Complaint
    {
        $currentStage = (int) $complaint->complaint_workflow;

        return DB::transaction(function () use ($complaint, $currentStage, $comment, $actionTakerId): Complaint {
            $complaint->rejected = 1;
            $complaint->complaint_workflow = 6;
            $complaint->reject_workflow = $currentStage;
            $complaint->is_closed = 0;
            $complaint->closed_by = null;
            $complaint->date_closed = null;
            $complaint->save();

            $this->recordChainOfCustody(
                complaintId: (string) $complaint->id,
                action: 'Reject Complaint',
                actionTakerId: $actionTakerId,
                workflowStage: getComplaintWorkflow()[6] ?? 'Cancelled',
                comments: $comment
            );

            return $complaint->fresh();
        });
    }

    public function recordChainOfCustody(
        string $complaintId,
        string $action,
        ?string $actionTakerId,
        string $workflowStage,
        ?string $comments = null
    ): void {
        $chain = new Chain_of_Custody_Complaint();
        $chain->complaint_id = $complaintId;
        $chain->action = $action;
        $chain->action_taker_id = $actionTakerId;
        $chain->workflow_stage = $workflowStage;
        $chain->comments = $comments;
        $chain->move_out_date = getTodayDate();
        $chain->save();
    }
}
