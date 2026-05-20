<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\SampleCapturedTestStagesTrack;
use App\Notifications\StageOvertime;
use App\User;

class CheckStageOvertime implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected string $trackId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $trackId)
    {
        $this->trackId = $trackId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Load the track fresh from database
        $track = SampleCapturedTestStagesTrack::find($this->trackId);
        
        if (!$track) {
            return;
        }
        
        // Refresh to get latest data
        $track->refresh();

        if (!in_array($track->status, ['running', 'overdue'], true) || !$track->testStage) {
            return;
        }

        $expectedEnd = $track->expected_end_at ?? $track->started_at?->copy()->addHours($track->testStage->duration_hours ?? 0);

        if (!$expectedEnd) {
            return;
        }

        if ($track->overtime_flagged_at) {
            return;
        }

        if (now()->lt($expectedEnd)) {
            \App\Jobs\CheckStageOvertime::dispatch($this->trackId)->delay($expectedEnd);
            return;
        }

        $track->markOverdue();

        $userId = $track->user_id ?? optional($track->stageHeaderRun)->user_id;

        if ($userId) {
            $track->startNextStageIfAvailable($userId);
        }

        if ($track->status === 'overdue' && $track->user_id) {
            $user = User::find($track->user_id);

            if ($user) {
                $user->notify(new StageOvertime($track));
            }
        }
    }
}
