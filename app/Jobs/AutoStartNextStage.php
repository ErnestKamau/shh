<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\SampleCapturedTestStagesTrack;
use App\Notifications\StageAutoStarted;
use App\User;

class AutoStartNextStage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected SampleCapturedTestStagesTrack $nextStageTrack;
    protected ?string $previousUserId;
    protected bool $autoStarted;

    /**
     * Create a new job instance.
     */
    public function __construct(SampleCapturedTestStagesTrack $nextStageTrack, ?string $previousUserId, bool $autoStarted = true)
    {
        $this->nextStageTrack = $nextStageTrack;
        $this->previousUserId = $previousUserId;
        $this->autoStarted = $autoStarted;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Refresh to get latest data
        $this->nextStageTrack->refresh();

        if (!in_array($this->nextStageTrack->status, ['pending', 'overdue'], true)) {
            return;
        }

        $userId = $this->previousUserId ?? optional($this->nextStageTrack->stageHeaderRun)->user_id;

        if (!$userId) {
            return;
        }

        $this->nextStageTrack->start($userId, $this->autoStarted);

        $user = User::find($userId);
        if ($user) {
            $user->notify(new StageAutoStarted(
                $this->nextStageTrack,
                $this->autoStarted
                    ? 'Automatically started after previous stage advanced.'
                    : 'Stage restarted by user.'
            ));
        }
    }
}
