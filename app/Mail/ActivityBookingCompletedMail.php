<?php

namespace App\Mail;

use App\Models\ActivityBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityBookingCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ActivityBooking $booking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Activity Completed - {$this->booking->booking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity-booking-completed',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
