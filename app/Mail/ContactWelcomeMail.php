<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $fullName;
    public string $emailAddress;
    public string $plainPassword;
    public string $companyName;
    public ?string $logoUrl;
    public string $loginUrl;

    public function __construct(string $fullName, string $emailAddress, string $plainPassword)
    {
        $this->fullName      = $fullName;
        $this->emailAddress  = $emailAddress;
        $this->plainPassword = $plainPassword;

        $activeCompany     = getActiveCompany();
        $this->companyName = $activeCompany?->name ?? config('app.name');
        $this->loginUrl = rtrim((string) config('services.customer_portal.url', 'https://amspec-portal.imaralims.com'), '/');

        if ($activeCompany && !empty($activeCompany->logo)) {
            $logoRaw = $activeCompany->logo;
            $this->logoUrl = str_starts_with($logoRaw, 'http')
                ? $logoRaw
                : rtrim(config('app.url'), '/') . '/' . ltrim($logoRaw, '/');
        } else {
            $this->logoUrl = null;
        }
    }

    public function build(): static
    {
        return $this
            ->subject('Welcome to ' . $this->companyName . ' Portal — Your Account is Ready')
            ->view('emails.contact_welcome');
    }
}
