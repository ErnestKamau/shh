<?php

namespace App\Notifications\DMS;

use App\Models\DMS\DocumentAmendment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AmendmentRequestedNotification extends Notification
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
            ->subject('Amendment Request for Document: ' . $this->amendment->document->title)
            ->line('An amendment has been requested for the document: ' . $this->amendment->document->title)
            ->line('Document Number: ' . $this->amendment->document->document_number)
            ->line('Reason: ' . $this->amendment->amendment_reason)
            ->line('Requested by: ' . $this->amendment->requester->name)
            ->action('Review Amendment', route('dms.amendments'))
            ->line('Please review and authorize this amendment request.');
    }

    public function toArray($notifiable): array
    {
        return [
            'amendment_id' => $this->amendment->id,
            'document_id' => $this->amendment->document_id,
            'document_title' => $this->amendment->document->title,
            'requester_name' => $this->amendment->requester->name,
            'amendment_reason' => $this->amendment->amendment_reason,
        ];
    }
}

