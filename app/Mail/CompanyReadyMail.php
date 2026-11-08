<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $ownerName,
        public string $companyName,
        public ?string $tenantUrl,
        public string $username,
        public string $password,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('main.wizard.company_ready_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.company-ready',
        );
    }
}
