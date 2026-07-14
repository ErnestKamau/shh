<?php

namespace App\Observers;

use App\Models\TrackSampleResult;
use App\Services\Sampleworkflow\SampleHeaderAssignmentService;

class TrackSampleResultObserver
{
    public function __construct(
        protected SampleHeaderAssignmentService $assignmentService,
    ) {}

    public function created(TrackSampleResult $trackSampleResult): void
    {
        $this->assignBatchWhenResultEntered($trackSampleResult, force: true);
    }

    public function updated(TrackSampleResult $trackSampleResult): void
    {
        $this->assignBatchWhenResultEntered($trackSampleResult);
    }

    /**
     * Method-sequence worksheet staging saves results here before they are posted
     * to captured_results. Assign the batch so it shows on the analyst's dashboard.
     */
    private function assignBatchWhenResultEntered(TrackSampleResult $trackSampleResult, bool $force = false): void
    {
        if (! $force && ! $trackSampleResult->wasChanged('result')) {
            return;
        }

        if (trim((string) ($trackSampleResult->result ?? '')) === '') {
            return;
        }

        $sampleHeaderId = $trackSampleResult->capturedResult?->sample_header_id;
        if ($sampleHeaderId === null || $sampleHeaderId === '') {
            return;
        }

        $this->assignmentService->assignOnResultEntry($sampleHeaderId);
    }
}
