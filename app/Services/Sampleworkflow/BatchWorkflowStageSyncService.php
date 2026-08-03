<?php

namespace App\Services\Sampleworkflow;

use App\ChainOfCustody;
use App\SampleAnalysisStage;
use App\SampleHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BatchWorkflowStageSyncService
{
    public function resolveTrackingStageId(SampleHeader $batch, string $workflowStatus): ?string
    {
        $stages = $batch->stages($workflowStatus);

        if (isset($stages[0]->id) && Str::isUuid((string) $stages[0]->id)) {
            return (string) $stages[0]->id;
        }

        $stage = SampleAnalysisStage::query()
            ->where('sample_workflow', $workflowStatus)
            ->orderBy('level', 'asc')
            ->first();

        if ($stage !== null && Str::isUuid((string) $stage->id)) {
            return (string) $stage->id;
        }

        return null;
    }

    public function applyWorkflowStatus(
        SampleHeader $batch,
        string $workflowStatus,
        ?string $comments = null,
        ?string $actingUserId = null,
    ): void {
        $previousStatus = (string) ($batch->status ?? '');
        $trackingStageId = $this->resolveTrackingStageId($batch, $workflowStatus);

        $batch->status = $workflowStatus;
        $batch->sample_tracking_stage = $trackingStageId;

        if ($previousStatus === $workflowStatus) {
            return;
        }

        $this->recordChainOfCustodyTransition(
            $batch,
            $workflowStatus,
            $trackingStageId,
            $comments ?? $this->defaultMoveComment($workflowStatus, $previousStatus),
            $actingUserId,
        );
    }

    /**
     * Close an open custody row that no longer matches the batch workflow status
     * and open a new row for the current status (heals skipped transitions).
     */
    public function ensureOpenCustodyMatchesWorkflow(
        SampleHeader $batch,
        ?string $comments = null,
        ?string $actingUserId = null,
    ): bool {
        $workflowStatus = (string) ($batch->status ?? '');
        if ($workflowStatus === '' || empty($batch->id)) {
            return false;
        }

        $open = ChainOfCustody::query()
            ->where('sample_header_id', $batch->id)
            ->whereNull('moved_out_date')
            ->orderByDesc('created_at')
            ->first();

        if ($open !== null && (string) $open->workflow_stage === $workflowStatus) {
            return false;
        }

        $trackingStageId = $batch->sample_tracking_stage;
        if ($trackingStageId === null || ! Str::isUuid((string) $trackingStageId)) {
            $trackingStageId = $this->resolveTrackingStageId($batch, $workflowStatus);
            $batch->sample_tracking_stage = $trackingStageId;
        }

        $this->recordChainOfCustodyTransition(
            $batch,
            $workflowStatus,
            is_string($trackingStageId) && Str::isUuid($trackingStageId) ? $trackingStageId : null,
            $comments ?? sprintf(
                'Chain of custody synchronized to current workflow stage (%s).',
                $workflowStatus
            ),
            $actingUserId,
        );

        return true;
    }

    public function recordChainOfCustodyTransition(
        SampleHeader $batch,
        string $workflowStatus,
        ?string $trackingStageId,
        ?string $comments = null,
        ?string $actingUserId = null,
    ): void {
        if (empty($batch->id)) {
            return;
        }

        $resolvedActingUserId = $this->resolveActingUserId($batch, $actingUserId);

        ChainOfCustody::query()
            ->where('sample_header_id', $batch->id)
            ->whereNull('moved_out_date')
            ->update([
                'moved_out_date' => now(),
                'moved_out_by' => $resolvedActingUserId,
            ]);

        $custody = new ChainOfCustody();
        $custody->sample_header_id = $batch->id;
        $custody->workflow_stage = $workflowStatus;
        $custody->tracking_stage_id = $trackingStageId;
        $custody->moved_in_by = $resolvedActingUserId;
        $custody->comments = $comments;
        $custody->save();
    }

    private function resolveActingUserId(SampleHeader $batch, ?string $actingUserId = null): string
    {
        $candidate = trim((string) ($actingUserId ?: Auth::id() ?: ''));
        if ($candidate !== '' && Str::isUuid($candidate)) {
            return $candidate;
        }

        $previous = ChainOfCustody::query()
            ->where('sample_header_id', $batch->id)
            ->whereNotNull('moved_in_by')
            ->orderByDesc('created_at')
            ->value('moved_in_by');

        $previousId = trim((string) ($previous ?? ''));
        if ($previousId !== '' && Str::isUuid($previousId)) {
            return $previousId;
        }

        throw new \RuntimeException(
            'Unable to record chain of custody without a valid acting user id (moved_in_by).'
        );
    }

    private function defaultMoveComment(string $workflowStatus, string $previousStatus): string
    {
        return sprintf(
            'Moved to %s (from %s).',
            $workflowStatus,
            $previousStatus !== '' ? $previousStatus : 'unknown'
        );
    }
}
