<?php

namespace App\Notifications\Registry;

use App\Models\Registry\RegistryRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistryRequestRejectedNotification extends Notification implements ShouldQueue
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
            ->subject('Returned: ' . $this->request->reference_no)
            ->line('Your registry request was returned for correction.')
            ->action('View', route('registry.requests.show', $this->request->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'registry_request_id' => $this->request->id,
            'reference_no' => $this->request->reference_no,
        ];
    }
}
