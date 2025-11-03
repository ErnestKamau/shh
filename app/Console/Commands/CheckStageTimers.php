<?php

namespace App\Console\Commands;

use App\Models\Worksheets\MethodSequenceRunStageData;
use App\Notifications\StageDurationExpired;
use App\Notifications\StageSafeDurationExpired;
use Illuminate\Console\Command;

class CheckStageTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:stage-timers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check method sequence stage timers and send notifications for expired durations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking stage timers...');

        // Get all in-progress stages
        $inProgressStages = MethodSequenceRunStageData::with(['run.analyst', 'stage'])
            ->where('status', 'in_progress')
            ->whereNotNull('time_in')
            ->get();

        $safeDurationNotifications = 0;
        $durationNotifications = 0;

        foreach ($inProgressStages as $stageData) {
            // Check safe duration expiration
            if ($stageData->isSafeDurationExpired() && !$stageData->safe_duration_alert_sent) {
                if ($stageData->run->analyst) {
                    $stageData->run->analyst->notify(new StageSafeDurationExpired($stageData));
                    $safeDurationNotifications++;
                }
                
                // Mark alert as sent
                $stageData->update(['safe_duration_alert_sent' => true]);
            }

            // Check total duration expiration
            if ($stageData->isDurationExpired() && !$stageData->duration_alert_sent) {
                if ($stageData->run->analyst) {
                    $stageData->run->analyst->notify(new StageDurationExpired($stageData));
                    $durationNotifications++;
                }
                
                // Mark alert as sent
                $stageData->update(['duration_alert_sent' => true]);
            }
        }

        $this->info("Processed {$inProgressStages->count()} in-progress stages");
        $this->info("Sent {$safeDurationNotifications} safe duration notifications");
        $this->info("Sent {$durationNotifications} duration notifications");

        return Command::SUCCESS;
    }
}
