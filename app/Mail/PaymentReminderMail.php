<?php

namespace App\Mail;

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
        public Order $order,
        public string $paymentUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Complete Your Payment - {$this->order->booking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlView: 'emails.payment-reminder',
            with: [
                'order' => $this->order,
                'paymentUrl' => $this->paymentUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
