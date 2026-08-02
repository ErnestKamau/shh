<?php

namespace App\Services\Lab;

use App\ChainOfCustody;
use App\Models\QuotationApprovalLog;
use App\Models\Sampleworkflow\SampleHeaderUserAssignment;
use Illuminate\Pagination\LengthAwarePaginator;

class PersonalDashboardHistoryService
{
    /**
     * @return LengthAwarePaginator<int, QuotationApprovalLog>
     */
    public function quotationApprovals(string $userId): LengthAwarePaginator
    {
        return QuotationApprovalLog::query()
            ->with([
                'quotationHeader.customer',
                'enquiry.submissionFormInstance.submissionForm',
            ])
            ->where('actor_user_id', $userId)
            ->whereIn('action', [
                QuotationApprovalLog::ACTION_APPROVED,
                QuotationApprovalLog::ACTION_REJECTED,
            ])
            ->latest('created_at')
            ->paginate(15, ['*'], 'quotation_history_page')
            ->withQueryString()
            ->fragment('activity-history');
    }

    /**
     * @return LengthAwarePaginator<int, SampleHeaderUserAssignment>
     */
    public function labAssignments(string $userId): LengthAwarePaginator
    {
        return SampleHeaderUserAssignment::query()
            ->forAssignee($userId)
            ->with([
                'sampleHeader.client',
                'sampleHeader.get_target_date',
                'assignedBy',
                'completedByUser',
            ])
            ->latest('created_at')
            ->paginate(15, ['*'], 'assignment_history_page')
            ->withQueryString()
            ->fragment('activity-history');
    }

    /**
     * @return LengthAwarePaginator<int, ChainOfCustody>
     */
    public function sampleVerifications(string $userId): LengthAwarePaginator
    {
        return $this->workflowCompletions(
            userId: $userId,
            workflowStage: 'Sample Verification',
            pageName: 'verification_history_page',
        );
    }

    /**
     * @return LengthAwarePaginator<int, ChainOfCustody>
     */
    public function sampleApprovals(string $userId): LengthAwarePaginator
    {
        return $this->workflowCompletions(
            userId: $userId,
            workflowStage: 'Sample Approval',
            pageName: 'approval_history_page',
        );
    }

    /**
     * @return LengthAwarePaginator<int, ChainOfCustody>
     */
    private function workflowCompletions(
        string $userId,
        string $workflowStage,
        string $pageName,
    ): LengthAwarePaginator {
        return ChainOfCustody::query()
            ->with([
                'sampleHeader.client',
                'sampleHeader.get_target_date',
            ])
            ->where('moved_out_by', $userId)
            ->where('workflow_stage', $workflowStage)
            ->whereNotNull('moved_out_date')
            ->latest('moved_out_date')
            ->paginate(15, ['*'], $pageName)
            ->withQueryString()
            ->fragment('activity-history');
    }
}
