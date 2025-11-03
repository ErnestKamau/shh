<?php

namespace App\Notifications\DMS;

use App\Models\DMS\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentArchivedNotification extends Notification
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
            ->subject('Document Archived: ' . $this->document->title)
            ->line('A document has been archived.')
            ->line('Document: ' . $this->document->title)
            ->line('Document Number: ' . $this->document->document_number)
            ->line('Archived by: ' . $this->document->archiver->name)
            ->line('Reason: ' . $this->document->archive_reason)
            ->action('View Archived Documents', route('dms.archived'))
            ->line('You can restore this document if needed.');
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_number' => $this->document->document_number,
            'archiver_name' => $this->document->archiver->name,
            'archive_reason' => $this->document->archive_reason,
        ];
    }
}

