<?php

namespace App\Mail;

use App\Models\DriverWithdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DriverWithdrawalStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public DriverWithdrawal $withdrawal,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Withdrawal '.strtoupper($this->withdrawal->status).' — Siwride',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.driver-withdrawal-status',
        );
    }
}
