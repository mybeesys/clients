<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationEmailCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $confirmUrl,
        public string $validFor,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('main.wizard.email_verify_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.registration-email-code',
        );
    }
}
