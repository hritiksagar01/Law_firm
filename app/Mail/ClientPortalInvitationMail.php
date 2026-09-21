<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPortalInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;

    public string $invitationUrl;

    public function __construct(Client $client, string $invitationUrl)
    {
        $this->client = $client;
        $this->invitationUrl = $invitationUrl;
    }

    public function envelope(): Envelope
    {
        $firmName = $this->client->firm->name ?? config('legal.app_name', 'Legal Chambers');

        return new Envelope(
            subject: "Welcome to {$firmName} — Access Your Client Portal",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client_portal_invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
