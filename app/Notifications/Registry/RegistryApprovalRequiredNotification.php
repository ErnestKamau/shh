<?php

namespace App\Notifications\Registry;

use App\Models\Registry\RegistryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistryApprovalRequiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RegistryRequest $request)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Approval Required: ' . $this->request->reference_no)
            ->line('A registry request requires your approval.')
            ->line('Stage: ' . ($this->request->current_stage ?? 'N/A'))
            ->action('Review', route('registry.approvals.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'registry_request_id' => $this->request->id,
            'reference_no' => $this->request->reference_no,
            'current_stage' => $this->request->current_stage,
        ];
    }
}
