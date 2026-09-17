<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;

    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    public function envelope(): Envelope
    {
        $matterTitle = $this->documentRequest->matter->title ?? 'Case File';
        return new Envelope(
            subject: "Action Required: Document Requested for {$matterTitle}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document_requested',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
