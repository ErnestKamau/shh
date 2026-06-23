<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestRequestReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $contactName,
        public string $reportNumber,
        public string $companyName,
        public ?string $downloadUrl,
        public ?string $notes,
    ) {}

    public function build(): self
    {
        return $this
            ->subject("Laboratory Test Report: {$this->reportNumber}")
            ->view('emails.test_request_report');
    }
}
