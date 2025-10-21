<?php

namespace App\Notifications\DMS;

use App\Models\DMS\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DocumentExpiringNotification extends Notification
{
    use Queueable;

    protected $document;
    protected $daysUntilExpiry;

    public function __construct(Document $document, int $daysUntilExpiry)
    {
        $this->document = $document;
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Document Expiring Soon: ' . $this->document->title)
            ->line('A document you own or manage is expiring soon.')
            ->line('Document: ' . $this->document->title)
            ->line('Document Number: ' . $this->document->document_number);

        if ($this->daysUntilExpiry === 0) {
            $message->line('This document **expires today**!');
        } else {
            $message->line("This document will expire in {$this->daysUntilExpiry} day(s).");
        }

        $message->line('Expiry Date: ' . $this->document->expiry_date->format('F d, Y'))
            ->action('View Document', route('dms.active'))
            ->line('Please take necessary action to renew or update this document.');

        return $message;
    }

    public function toArray($notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_number' => $this->document->document_number,
            'expiry_date' => $this->document->expiry_date->toDateString(),
            'days_until_expiry' => $this->daysUntilExpiry,
        ];
    }
}

