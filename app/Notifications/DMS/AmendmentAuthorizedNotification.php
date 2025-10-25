<?php

namespace App\Notifications\DMS;

use App\Models\DMS\DocumentAmendment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AmendmentAuthorizedNotification extends Notification
{
    use Queueable;

    protected $amendment;

    public function __construct(DocumentAmendment $amendment)
    {
        $this->amendment = $amendment;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Amendment Authorized: ' . $this->amendment->document->title)
            ->line('Your amendment request has been authorized.')
            ->line('Document: ' . $this->amendment->document->title)
            ->line('Authorized by: ' . $this->amendment->authorizer->name)
            ->action('Upload Amended File', route('dms.amendments'))
            ->line('You can now upload the amended file.');
    }

    public function toArray($notifiable): array
    {
        return [
            'amendment_id' => $this->amendment->id,
            'document_id' => $this->amendment->document_id,
            'document_title' => $this->amendment->document->title,
            'authorizer_name' => $this->amendment->authorizer->name,
        ];
    }
}

