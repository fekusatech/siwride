<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking Confirmed - {$this->order->booking_code}",
        );
    }

    public function content(): Content
    {
        $linkedOrder = $this->order->linkedOrder;
        $isRoundTrip = $this->order->trip_type === 'round_trip' && $linkedOrder;
        $grandTotal = $isRoundTrip
            ? (float) $this->order->price + (float) $linkedOrder->price
            : (float) $this->order->price;

        return new Content(
            htmlView: 'emails.booking-confirmed',
            with: [
                'order' => $this->order,
                'linkedOrder' => $linkedOrder,
                'isRoundTrip' => $isRoundTrip,
                'grandTotal' => $grandTotal,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
