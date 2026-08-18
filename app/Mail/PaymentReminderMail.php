<?php

namespace App\Mail;

use App\Models\ActivityBooking;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order|ActivityBooking $order,
        public string $paymentUrl,
        public bool $isActivity = false,
    ) {}

    public function envelope(): Envelope
    {
        $code = $this->order->booking_code;
        $subject = $this->isActivity
            ? "Complete Your Activity Booking Payment - {$code}"
            : "Complete Your Payment - {$code}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'order' => $this->order,
                'paymentUrl' => $this->paymentUrl,
                'isActivity' => $this->isActivity,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
