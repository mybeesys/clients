<?php

namespace Modules\Administration\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionAdminEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $domain;
    public $db_name;
    public $company;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->domain = $data['domain'];
        $this->db_name = $data['db_name'];
        $this->company = $data['company'];
    }

    public function build()
    {
        return $this->view('administration::emails.subscription_create_subdomain_email')
            ->with([
                'domain' => $this->domain,
                'db_name' => $this->db_name,
                'company' => $this->company,
            ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Subscription Admin Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'administration::emails.subscription_create_subdomain_email',
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
