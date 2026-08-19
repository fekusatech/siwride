<?php

namespace App\Mail;

use App\Models\DriverServiceBooking;
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
        public Order|DriverServiceBooking $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Booking Confirmed - {$this->order->booking_code}",
        );
    }

    public function content(): Content
    {
        $isService = $this->order instanceof DriverServiceBooking;

        if ($isService) {
            return new Content(
                view: 'emails.booking-confirmed',
                with: [
                    'order' => $this->order,
                    'linkedOrder' => null,
                    'isRoundTrip' => false,
                    'grandTotal' => (float) $this->order->total_amount,
                    'isService' => true,
                ],
            );
        }

        $linkedOrder = $this->order->linkedOrder;
        $isRoundTrip = $this->order->trip_type === 'round_trip' && $linkedOrder;
        $grandTotal = $isRoundTrip
            ? (float) $this->order->price + (float) $linkedOrder->price
            : (float) $this->order->price;

        return new Content(
            view: 'emails.booking-confirmed',
            with: [
                'order' => $this->order,
                'linkedOrder' => $linkedOrder,
                'isRoundTrip' => $isRoundTrip,
                'grandTotal' => $grandTotal,
                'isService' => false,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
