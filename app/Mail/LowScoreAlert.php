<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowScoreAlert extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $feedback;
    public $lowRatings;
    public $correctiveActionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($feedback, $lowRatings = [], $correctiveActionUrl = null)
    {
        $this->feedback = $feedback;
        $this->lowRatings = $lowRatings;
        $this->correctiveActionUrl = $correctiveActionUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Feedback Alert: Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.low_score_alert',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
