<?php

namespace App\Notifications\DMS;

use App\Models\DMS\DocumentAmendment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AmendmentApprovedNotification extends Notification
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
        $status = $this->amendment->status === 'approved' ? 'Approved' : 'Rejected';
        
        return (new MailMessage)
            ->subject("Amendment {$status}: " . $this->amendment->document->title)
            ->line("Your amendment has been {$status}.")
            ->line('Document: ' . $this->amendment->document->title)
            ->line($status . ' by: ' . $this->amendment->approver->name)
            ->action('View Document', route('dms.active'))
            ->line('Thank you for your submission.');
    }

    public function toArray($notifiable): array
    {
        return [
            'amendment_id' => $this->amendment->id,
            'document_id' => $this->amendment->document_id,
            'document_title' => $this->amendment->document->title,
            'approver_name' => $this->amendment->approver->name,
            'status' => $this->amendment->status,
        ];
    }
}

