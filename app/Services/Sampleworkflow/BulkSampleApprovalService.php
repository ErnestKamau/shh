<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\SampleHeader;
use App\User;

/**
 * Bulk-approve Sample Approval jobs from the workflow board (Brazil + UAE).
 */
final class BulkSampleApprovalService
{
    /**
     * @param  list<string>  $batchIds
     * @return array{approved: int, skipped: list<string>}
     */
    public function approve(array $batchIds, User $actor): array
    {
        $ids = collect($batchIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $approved = 0;
        $skipped = [];

        foreach ($ids as $batchId) {
            $batch = SampleHeader::query()->with('approvers')->find($batchId);
            if (! $batch) {
                $skipped[] = $batchId.' (not found)';
                continue;
            }

            if ((string) $batch->status !== 'Sample Approval') {
                $skipped[] = $batch->batch_code.' (not in Sample Approval)';
                continue;
            }

            if ($batch->hasCompletedSampleApproval()) {
                $skipped[] = $batch->batch_code.' (already approved)';
                continue;
            }

            if ((string) ($batch->verify_user_id ?? '') !== ''
                && (string) $batch->verify_user_id === (string) $actor->id) {
                $skipped[] = $batch->batch_code.' (verifier cannot approve the same job)';
                continue;
            }

            $approvers = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Approval')
                ->orderBy('approver_order')
                ->get();

            if ($approvers->isEmpty()) {
                $approver = new BatchLabSectionApprover();
                $approver->user_id = $actor->id;
                $approver->title = 'Authorized by';
                $approver->lab_section_ids = 0;
                $approver->batch_id = $batch->id;
                $approver->batch_status = 'Sample Approval';
                $approver->show_report = 1;
                $approver->status = 1;
                $approver->approval_date = now();
                $approver->save();
            } else {
                foreach ($approvers as $approver) {
                    if ((int) $approver->status === 1) {
                        continue;
                    }

                    $approver->user_id = $actor->id;
                    $approver->status = 1;
                    $approver->approval_date = now();
                    $approver->save();
                }
            }

            $batch->approve_user_id = $actor->id;
            if (function_exists('getTodayDate')) {
                $batch->approval_date = getTodayDate();
            } else {
                $batch->approval_date = now()->toDateString();
            }
            $batch->save();
            $approved++;
        }

        return [
            'approved' => $approved,
            'skipped' => $skipped,
        ];
    }
}
