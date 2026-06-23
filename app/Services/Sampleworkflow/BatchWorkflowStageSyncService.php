<?php

namespace App\Services\Sampleworkflow;

use App\SampleAnalysisStage;
use App\SampleHeader;
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

    public function applyWorkflowStatus(SampleHeader $batch, string $workflowStatus): void
    {
        $batch->status = $workflowStatus;
        $batch->sample_tracking_stage = $this->resolveTrackingStageId($batch, $workflowStatus);
    }
}
