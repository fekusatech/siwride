<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminderMail;
use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\Setting;
use App\Services\VoucherService;
use App\Support\DriverReferralAttribution;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class DriverServiceBookingController extends Controller
{
    public function show(string $slug): Response
    {
        $service = DriverService::where('slug', $slug)->where('status', DriverService::STATUS_APPROVED)->firstOrFail();

        $customer = Auth::guard('customer')->user();

        return Inertia::render('customer/service-detail', [
            'service' => $service->load('driver'),
            'payment' => [
                'dp_percent' => (float) ($service->dp_percent ?? Setting::getValue('dp_percent_default', 30)),
            ],
            'customer' => $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $service = DriverService::where('slug', $slug)->where('status', DriverService::STATUS_APPROVED)->firstOrFail();

        $validated = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'pax' => ['required', 'integer', 'min:'.$service->min_pax],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'voucher_code' => ['nullable', 'string', 'max:20'],
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($service->max_pax && $validated['pax'] > $service->max_pax) {
            throw ValidationException::withMessages([
                'pax' => "Maximum {$service->max_pax} participants per booking.",
            ]);
        }

        $customer = Customer::where('email', $validated['customer_email'])->first();

        if ($request->boolean('create_account') && $customer && $customer->password) {
            throw ValidationException::withMessages([
                'customer_email' => 'This email is already registered. Please log in to continue.',
            ]);
        }

        $customerData = ['name' => $validated['customer_name'], 'phone' => $validated['customer_phone'] ?? null];

        if ($request->boolean('create_account') && isset($validated['password'])) {
            $customerData['password'] = Hash::make($validated['password']);
        }

        if ($customer) {
            $updateData = ['name' => $customerData['name']];
            if (isset($customerData['phone'])) {
                $updateData['phone'] = $customerData['phone'];
            }
            if (! $customer->password && isset($customerData['password'])) {
                $updateData['password'] = $customerData['password'];
            }
            $customer->update($updateData);
        } else {
            $customerData['email'] = $validated['customer_email'];
            $customer = Customer::create($customerData);
        }

        $subtotal = round((float) $service->price_per_pax * $validated['pax'], 2);
        $discountAmount = 0.0;
        $voucher = null;
        $voucherCode = strtoupper(trim($validated['voucher_code'] ?? ''));

        if ($voucherCode !== '') {
            $voucherResult = app(VoucherService::class)->validate(
                $voucherCode,
                $subtotal,
                $validated['customer_email'],
            );
            $voucher = $voucherResult['voucher'];
            $discountAmount = $voucherResult['discount_amount'];
        }

        $totalAmount = round($subtotal - $discountAmount, 2);
        $dpPercent = (float) ($service->dp_percent ?? Setting::getValue('dp_percent_default', 30));
        $dpAmount = round($totalAmount * ($dpPercent / 100), 2);
        $remainingCash = round($totalAmount - $dpAmount, 2);

        $booking = DriverServiceBooking::create([
            'booking_code' => $this->generateUniqueBookingCode(),
            'driver_service_id' => $service->id,
            'customer_id' => $customer->id,
            'booking_date' => $validated['booking_date'],
            'pax' => $validated['pax'],
            'price_per_pax' => $service->price_per_pax,
            'total_price' => $totalAmount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'voucher_code' => $voucher?->code,
            'total_amount' => $totalAmount,
            'dp_percent' => $dpPercent,
            'dp_amount' => $dpAmount,
            'remaining_cash' => $remainingCash,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'],
            'notes' => $validated['notes'] ?? null,
            'status' => DriverServiceBooking::STATUS_PENDING,
            'payment_status' => DriverServiceBooking::PAYMENT_PENDING,
        ]);

        DriverReferralAttribution::attributeService($booking);

        if ($voucher !== null) {
            app(VoucherService::class)->redeem(
                $voucher,
                $booking,
                $validated['customer_email'],
                $subtotal,
            );
        }

        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        try {
            $paymentUrl = $this->generateXenditPayment($booking, $service);

            try {
                Mail::to($booking->customer_email)->send(new PaymentReminderMail($booking, $paymentUrl, isService: true));
            } catch (\Throwable $exception) {
                Log::error('Failed to send service booking payment reminder email', [
                    'booking_code' => $booking->booking_code,
                    'error' => $exception->getMessage(),
                ]);
            }

            return inertia()->location($paymentUrl);
        } catch (\Exception $e) {
            return redirect()->route('driver-services.show', $slug)
                ->with('error', 'Failed to create payment: '.$e->getMessage());
        }
    }

    private function generateXenditPayment(DriverServiceBooking $booking, DriverService $service): string
    {
        $xenditKey = Setting::getValue('xendit_secret_key') ?: config('services.xendit.secret_key');
        Configuration::setXenditKey($xenditKey);

        $guzzleClient = new Client(['verify' => ! app()->environment('local')]);
        $apiInstance = new InvoiceApi($guzzleClient);

        $successUrl = route('driver-services.booking.success', $booking->booking_code);
        $failureUrl = route('driver-services.show', $service->slug).'?payment=failed';

        $req = new CreateInvoiceRequest([
            'external_id' => $booking->booking_code.'_'.time(),
            'amount' => (float) $booking->dp_amount,
            'payer_email' => $booking->customer_email,
            'description' => sprintf(
                'Service Booking: %s — %s, DP %s%% dari total Rp %s - sisa tunai ke driver Rp %s',
                $service->title,
                $booking->booking_code,
                rtrim(rtrim(number_format((float) $booking->dp_percent, 2, '.', ''), '0'), '.'),
                number_format((float) $booking->total_amount, 0, ',', '.'),
                number_format((float) $booking->remaining_cash, 0, ',', '.'),
            ),
            'success_redirect_url' => $successUrl,
            'failure_redirect_url' => $failureUrl,
        ]);

        $result = $apiInstance->createInvoice($req);
        $invoiceUrl = $result->getInvoiceUrl();

        $booking->update([
            'payment_method' => 'Xendit Invoice',
            'payment_reference' => $invoiceUrl,
        ]);

        return $invoiceUrl;
    }

    public function validateVoucher(Request $request, string $slug): JsonResponse
    {
        $service = DriverService::where('slug', $slug)->where('status', DriverService::STATUS_APPROVED)->firstOrFail();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'pax' => ['required', 'integer', 'min:1'],
            'email' => ['nullable', 'email'],
        ]);

        $subtotal = round((float) $service->price_per_pax * $validated['pax'], 2);

        try {
            $result = app(VoucherService::class)->validate(
                strtoupper(trim($validated['code'])),
                $subtotal,
                $validated['email'] ?? null,
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'valid' => false,
                'message' => $exception->errors()['voucher_code'][0] ?? 'Voucher tidak valid.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'code' => $result['voucher']->code,
            'discount_amount' => $result['discount_amount'],
            'subtotal' => $subtotal,
            'total_amount' => round($subtotal - $result['discount_amount'], 2),
        ]);
    }

    public function success(string $bookingCode): Response
    {
        $booking = DriverServiceBooking::with('driverService')
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/service-booking-success', [
            'booking' => $booking,
        ]);
    }

    public function detail(string $bookingCode): Response
    {
        $booking = DriverServiceBooking::with(['driverService', 'assignedDriver'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/service-booking-detail', [
            'booking' => $booking,
            'assigned_driver' => $booking->assignedDriver !== null && $booking->status === DriverServiceBooking::STATUS_CONFIRMED
                ? [
                    'id' => $booking->assignedDriver->getKey(),
                    'name' => $booking->assignedDriver->name,
                    'email' => $booking->assignedDriver->email,
                    'phone' => $booking->assignedDriver->phone,
                    'image' => $booking->assignedDriver->image,
                ]
                : null,
        ]);
    }

    private function generateUniqueBookingCode(): string
    {
        do {
            $code = 'SVC-'.strtoupper(Str::random(10));
        } while (DriverServiceBooking::where('booking_code', $code)->exists());

        return $code;
    }
}
