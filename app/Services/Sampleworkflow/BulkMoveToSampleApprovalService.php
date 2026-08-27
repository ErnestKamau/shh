<?php

namespace App\Services\Sampleworkflow;

use App\BatchLabSectionApprover;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\User;

final class BulkMoveToSampleApprovalService
{
    public function __construct(
        private readonly BatchWorkflowStageSyncService $stageSync,
    ) {
    }

    /**
     * Move Technical-Reviewer-verified jobs from Sample Verification to Sample Approval.
     *
     * @param  list<string>  $batchIds
     * @return array{moved: int, skipped: list<string>}
     */
    public function move(array $batchIds, User $actor): array
    {
        $ids = collect($batchIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $moved = 0;
        $skipped = [];

        foreach ($ids as $batchId) {
            $batch = SampleHeader::query()->find($batchId);
            if (! $batch) {
                $skipped[] = $batchId.' (not found)';
                continue;
            }

            if ((string) $batch->status === 'Sample Approval') {
                $skipped[] = $batch->batch_code.' (already in Sample Approval)';
                continue;
            }

            if ((string) $batch->status !== 'Sample Verification') {
                $skipped[] = $batch->batch_code.' (not in Sample Verification)';
                continue;
            }

            $technicalReviewerApproved = BatchLabSectionApprover::query()
                ->where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Verification')
                ->where('is_technical_reviewer', true)
                ->where('status', 1)
                ->exists();

            if (! $technicalReviewerApproved) {
                $skipped[] = $batch->batch_code.' (not verified yet)';
                continue;
            }

            $this->ensureLabManagerVerificationApprover($batch, $actor);
            $approver = $this->ensureSampleApprovalApprover($batch, $actor);

            if (function_exists('getTodayDate')) {
                $batch->report_verified_date = getTodayDate();
            }
            $batch->verify_user_id = $actor->id;
            if ($approver !== null) {
                $batch->approve_user_id = $approver->user_id;
            }

            $this->stageSync->applyWorkflowStatus(
                $batch,
                'Sample Approval',
                'Moved to Sample Approval from Sample Verification.',
                (string) $actor->id,
            );
            $batch->save();
            $moved++;
        }

        return [
            'moved' => $moved,
            'skipped' => $skipped,
        ];
    }

    private function ensureLabManagerVerificationApprover(SampleHeader $batch, User $actor): void
    {
        $exists = BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->where('can_send_back_to_lab', true)
            ->exists();

        if ($exists) {
            return;
        }

        $labManager = $this->resolveLabManager($batch, $actor);

        $lm = new BatchLabSectionApprover();
        $lm->user_id = $labManager->id;
        $lm->title = 'Lab Manager';
        $lm->lab_section_ids = (string) ($batch->lab_section_ids ?: 0);
        $lm->batch_id = $batch->id;
        $lm->batch_status = 'Sample Verification';
        $lm->status = 0;
        $lm->approver_order = 2;
        $lm->is_technical_reviewer = false;
        $lm->can_send_back_to_lab = true;
        $lm->show_report = 1;
        $lm->approver_type = 'Lab Manager';
        $lm->save();
    }

    private function ensureSampleApprovalApprover(SampleHeader $batch, User $actor): ?BatchLabSectionApprover
    {
        $existing = BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Approval')
            ->orderBy('approver_order')
            ->first();

        if ($existing) {
            return $existing;
        }

        $approverUser = $this->resolveLabManager($batch, $actor);

        $approver = new BatchLabSectionApprover();
        $approver->status = 0;
        $approver->user_id = $approverUser->id;
        $approver->title = 'Authorized by';
        $approver->lab_section_ids = 0;
        $approver->batch_id = $batch->id;
        $approver->batch_status = 'Sample Approval';
        $approver->show_report = 1;
        $approver->save();

        return $approver;
    }

    private function resolveLabManager(SampleHeader $batch, User $actor): User
    {
        $labManager = User::query()
            ->where('active', 1)
            ->whereHas('roles', function ($query): void {
                $query->where('name', 'like', '%Lab Manager%');
            })
            ->first();

        if ($labManager) {
            return $labManager;
        }

        $sectionIds = array_values(array_filter(array_map('trim', explode(',', (string) $batch->lab_section_ids))));
        if ($sectionIds !== []) {
            $section = SampleAnalysisStage::query()->whereIn('id', $sectionIds)->first();
            if ($section && $section->section_head_id) {
                $head = User::query()->find($section->section_head_id);
                if ($head) {
                    return $head;
                }
            }
        }

        $technicalReviewer = BatchLabSectionApprover::query()
            ->where('batch_id', $batch->id)
            ->where('batch_status', 'Sample Verification')
            ->where('is_technical_reviewer', true)
            ->first();

        if ($technicalReviewer) {
            $reviewer = User::query()->find($technicalReviewer->user_id);
            if ($reviewer) {
                return $reviewer;
            }
        }

        return $actor;
    }
}
