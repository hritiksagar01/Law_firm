<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentScheduledMail extends Mailable
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
            subject: "Appointment Confirmed: {$this->appointment->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment_scheduled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
