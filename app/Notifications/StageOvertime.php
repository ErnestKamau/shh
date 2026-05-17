<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SampleCapturedTestStagesTrack;

class StageOvertime extends Notification implements ShouldQueue
{
    use Queueable;

    protected $track;

    /**
     * Create a new notification instance.
     */
    public function __construct(SampleCapturedTestStagesTrack $track)
    {
        $this->track = $track;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $testStage = $this->track->testStage;
        $stageHeader = $this->track->stageHeader;
        $sampleCode = $this->track->sampleDetail->sample_code ?? 'Unknown';
        
        return (new MailMessage)
            ->subject('Stage Overtime Alert: ' . $testStage->stage_name)
            ->markdown('notifications.stage-overtime', [
                'track' => $this->track,
                'testStage' => $testStage,
                'stageHeader' => $stageHeader,
                'sampleCode' => $sampleCode,
                'userName' => $notifiable->name,
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $testStage = $this->track->testStage;
        $stageHeader = $this->track->stageHeader;
        
        return [
            'message' => 'Stage "' . $testStage->stage_name . '" for method sequence "' . $stageHeader->name . '" has exceeded its expected duration',
            'track_id' => $this->track->id,
            'test_stage_id' => $this->track->test_stage_id,
            'stage_header_id' => $this->track->stage_header_id,
            'sample_code' => $this->track->sampleDetail->sample_code ?? 'Unknown',
            'started_at' => $this->track->started_at->toDateTimeString(),
            'expected_duration' => $testStage->duration_hours . ' hours',
        ];
    }
}
