<?php

namespace App\Notifications\DMS;

use App\Models\DMS\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentApprovalRequiredNotification extends Notification
{
    use Queueable;

    protected $document;

    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Document Approval Required: ' . $this->document->title)
            ->line('A document requires your approval.')
            ->line('Document: ' . $this->document->title)
            ->line('Document Number: ' . $this->document->document_number)
            ->line('Submitted by: ' . $this->document->creator->name)
            ->action('Review Document', route('dms.active'))
            ->line('Please review and approve this document.');
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_number' => $this->document->document_number,
            'creator_name' => $this->document->creator->name,
        ];
    }
}

