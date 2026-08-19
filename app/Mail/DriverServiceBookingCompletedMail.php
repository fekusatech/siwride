<?php

namespace App\Mail;

use App\Models\DriverServiceBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverServiceBookingCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DriverServiceBooking $booking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Service Booking Completed - {$this->booking->booking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.driver-service-booking-completed',
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
