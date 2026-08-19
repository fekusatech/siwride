<?php

namespace App\Mail;

use App\Models\DriverServiceReassignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverServiceReassignRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DriverServiceReassignment $reassignment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Service Reassignment Rejected - {$this->reassignment->booking->booking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.driver-service-reassign-rejected',
            with: [
                'reassignment' => $this->reassignment,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
