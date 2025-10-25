<?php

namespace App\Notifications;

use App\Models\Worksheets\MethodSequenceRunStageData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StageSafeDurationExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MethodSequenceRunStageData $stageData
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $run = $this->stageData->run;
        $stage = $this->stageData->stage;
        
        return (new MailMessage)
            ->subject('Stage Safe Duration Expired - ' . $run->run_name)
            ->line("The safe duration for stage '{$stage->name}' in run '{$run->run_name}' has expired.")
            ->line('Please check the stage progress and take necessary action.')
            ->action('View Run', route('method-sequence-worksheet', ['runId' => $run->id]))
            ->line('This is an automated notification from the Laboratory Management System.');
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $run = $this->stageData->run;
        $stage = $this->stageData->stage;
        
        return [
            'type' => 'stage_safe_duration_expired',
            'title' => 'Stage Safe Duration Expired',
            'message' => "Safe duration expired for stage '{$stage->name}' in run '{$run->run_name}'",
            'run_id' => $run->id,
            'stage_data_id' => $this->stageData->id,
            'stage_name' => $stage->name,
            'run_name' => $run->run_name,
            'action_url' => route('method-sequence-worksheet', ['runId' => $run->id]),
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
