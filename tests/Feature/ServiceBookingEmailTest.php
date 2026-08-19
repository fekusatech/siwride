<?php

use App\Mail\BookingConfirmedMail;
use App\Mail\PaymentReminderMail;
use App\Models\DriverServiceBooking;
use Illuminate\Support\Facades\Mail;

it('renders discount row in service confirmed email only when voucher applied', function () {
    $withVoucher = DriverServiceBooking::factory()->create([
        'voucher_code' => 'SAVE10',
        'discount_amount' => 20000,
        'total_amount' => 180000,
        'dp_amount' => 54000,
        'remaining_cash' => 126000,
    ]);

    $withoutVoucher = DriverServiceBooking::factory()->create([
        'voucher_code' => null,
        'discount_amount' => 0,
    ]);

    $withHtml = (new BookingConfirmedMail($withVoucher))->render();
    $withoutHtml = (new BookingConfirmedMail($withoutVoucher))->render();

    expect($withHtml)->toContain('Discount (SAVE10)')
        ->and($withHtml)->toContain('− IDR 20.000')
        ->and($withHtml)->toContain('Dibayar sekarang (DP 30%)')
        ->and($withHtml)->toContain('IDR 54.000')
        ->and($withHtml)->toContain('Sisa tunai ke driver')
        ->and($withHtml)->toContain('IDR 126.000')
        ->and($withoutHtml)->not->toContain('Discount (')
        ->and($withoutHtml)->toContain('Dibayar sekarang (DP 30%)');
});

it('renders discount row in service payment reminder email only when voucher applied', function () {
    $booking = DriverServiceBooking::factory()->create([
        'voucher_code' => 'SAVE10',
        'discount_amount' => 20000,
        'total_amount' => 180000,
        'dp_amount' => 54000,
        'remaining_cash' => 126000,
    ]);

    $mail = new PaymentReminderMail($booking, 'https://xendit.test/pay', isService: true);
    $html = $mail->render();

    expect($mail->envelope()->subject)->toBe('Complete Your Service Booking Payment - '.$booking->booking_code)
        ->and($html)->toContain('Discount (SAVE10)')
        ->and($html)->toContain('− IDR 20.000')
        ->and($html)->toContain('Bayar sekarang (DP 30%)')
        ->and($html)->toContain('IDR 54.000')
        ->and($html)->toContain('Sisa tunai ke driver')
        ->and($html)->toContain('IDR 126.000');
});

it('sends service booking confirmation email to customer on webhook paid', function () {
    Mail::fake();

    $booking = DriverServiceBooking::factory()->create([
        'status' => DriverServiceBooking::STATUS_PENDING,
        'payment_status' => DriverServiceBooking::PAYMENT_PENDING,
    ]);

    $this->postJson('/api/webhooks/xendit/invoice', [
        'status' => 'PAID',
        'external_id' => $booking->booking_code.'_'.time(),
    ])->assertOk();

    Mail::assertSent(BookingConfirmedMail::class, function ($mail) use ($booking) {
        return $mail->hasTo($booking->customer_email)
            && $mail->order->is($booking);
    });
});
