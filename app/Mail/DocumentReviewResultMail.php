<?php

namespace App\Mail;

use App\Models\DocumentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentReviewResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public DocumentRequest $documentRequest;

    public string $action; // accepted or rejected

    public function __construct(DocumentRequest $documentRequest, string $action)
    {
        $this->documentRequest = $documentRequest;
        $this->action = $action;
    }

    public function envelope(): Envelope
    {
        $statusText = $this->action === 'accepted' ? 'Accepted' : 'Action Required: Re-submission Requested';
        $matterTitle = $this->documentRequest->matter->title ?? 'Case File';

        return new Envelope(
            subject: "Update: Document {$this->documentRequest->title} {$statusText} ({$matterTitle})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.document_review_result',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
