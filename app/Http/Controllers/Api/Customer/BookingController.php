<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Customer\RetryBookingPaymentRequest;
use App\Http\Requests\Api\Customer\StoreBookingRequest;
use App\Http\Requests\Api\Customer\TrackBookingRequest;
use App\Http\Resources\Api\Customer\BookingResource;
use App\Mail\PaymentReminderMail;
use App\Models\Order;
use App\Services\CustomerOrderPaymentService;
use App\Services\CustomerTransferBookingService;
use App\Services\OrderCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function __construct(
        private CustomerTransferBookingService $bookings,
        private CustomerOrderPaymentService $payments,
        private OrderCancellationService $cancellations,
    ) {}

    public function store(StoreBookingRequest $request): JsonResponse
    {
        $order = $this->bookings->create($request->validated());
        $paymentUrl = null;

        try {
            $paymentUrl = $this->payments->createInvoice($order);
            Mail::to($order->customer_email)->send(new PaymentReminderMail($order, $paymentUrl));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'message' => $paymentUrl
                ? 'Booking created. Continue to payment.'
                : 'Booking created, but payment is temporarily unavailable.',
            'data' => (new BookingResource($order->fresh([
                'vehicleCategory',
                'driver',
                'linkedOrder.vehicleCategory',
            ])))->resolve(),
            'payment_url' => $paymentUrl,
        ], 201);
    }

    public function track(TrackBookingRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order = $this->ownedOrder(
            $validated['booking_code'],
            $validated['email'],
        );

        if (! $order) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        $this->cancellations->autoCancelIfEligible($order);

        return response()->json([
            'data' => (new BookingResource($order->fresh([
                'vehicleCategory',
                'driver',
                'linkedOrder.vehicleCategory',
            ])))->resolve(),
        ]);
    }

    public function retryPayment(
        RetryBookingPaymentRequest $request,
        string $bookingCode,
    ): JsonResponse {
        $order = $this->ownedOrder($bookingCode, $request->validated('email'));

        if (! $order) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($order->status !== 'pending' || $order->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Payment cannot be created for this booking.',
            ], 422);
        }

        try {
            $paymentUrl = $this->payments->createInvoice($order);
        } catch (\Throwable $exception) {
            Log::error('Customer API payment retry failed.', [
                'booking_code' => $order->booking_code,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'Payment is temporarily unavailable. Please try again.',
            ], 503);
        }

        return response()->json([
            'message' => 'Payment link created.',
            'data' => ['payment_url' => $paymentUrl],
        ]);
    }

    public function cancel(
        RetryBookingPaymentRequest $request,
        string $bookingCode,
    ): JsonResponse {
        $order = $this->ownedOrder($bookingCode, $request->validated('email'));

        if (! $order) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if (! $this->cancellations->canBeCancelled($order)) {
            return response()->json([
                'message' => 'This booking can no longer be cancelled.',
            ], 422);
        }

        if (! $this->cancellations->manuallyCancel($order)) {
            return response()->json([
                'message' => 'Booking cancellation failed. Please try again.',
            ], 503);
        }

        return response()->json([
            'message' => 'Booking cancelled.',
            'data' => (new BookingResource($order->fresh([
                'vehicleCategory',
                'driver',
                'linkedOrder.vehicleCategory',
            ])))->resolve(),
        ]);
    }

    private function ownedOrder(string $bookingCode, string $email): ?Order
    {
        return Order::query()
            ->where('booking_code', strtoupper(trim($bookingCode)))
            ->where('customer_email', trim($email))
            ->where('is_return_trip', false)
            ->first();
    }
}
