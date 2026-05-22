<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackCampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $subject;
    public $recipientName;
    public $feedbackLink;
    public $company;
    public $companyName;
    public $companyAddress;
    public $companyWebsite;
    public $logoUrl;

    public function __construct($body, $subject, $recipientName, $feedbackLink, $company = null)
    {
        $this->body = $body;
        $this->subject = $subject;
        $this->recipientName = $recipientName;
        $this->feedbackLink = $feedbackLink;
        $this->company = $company;

        $activeCompany = $company ?: getActiveCompany();
        $this->companyName = $activeCompany?->name ?? config('app.name');
        $this->companyAddress = $activeCompany?->address ?? '';
        $this->companyWebsite = $activeCompany?->website ?? '';

        if ($activeCompany && !empty($activeCompany->logo)) {
            $logoRaw = $activeCompany->logo;
            $this->logoUrl = str_starts_with($logoRaw, 'http')
                ? $logoRaw
                : rtrim(config('app.url'), '/') . '/' . ltrim($logoRaw, '/');
        } else {
            $this->logoUrl = null;
        }
    }

    public function build()
    {
        return $this
            ->subject($this->subject)
            ->view('emails.feedback_campaign');
    }
}
