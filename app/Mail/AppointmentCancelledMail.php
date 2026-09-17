<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Notice: Appointment Cancelled — {$this->appointment->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment_cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
