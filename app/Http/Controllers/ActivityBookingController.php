<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminderMail;
use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\PackTierService;
use App\Services\VoucherService;
use App\Services\WhatsAppService;
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

class ActivityBookingController extends Controller
{
    public function show(string $slug): Response
    {
        $activity = Activity::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $customer = Auth::guard('customer')->user();

        $relatedActivities = Activity::where('is_active', true)
            ->where('id', '!=', $activity->id)
            ->whereNotNull('price_per_pax')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(3)
            ->get();

        return Inertia::render('customer/activity-detail', [
            'activity' => $activity,
            'relatedActivities' => $relatedActivities,
            'payment' => [
                'dp_percent' => (float) Setting::getValue('dp_percent_default', 30),
            ],
            'packTiers' => app(PackTierService::class)->allTiersForDisplay(),
            'customer' => $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ] : null,
        ]);
    }

    public function store(Request $request, string $slug)
    {
        $activity = Activity::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'pax' => ['required', 'integer', 'min:'.$activity->min_pax],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'create_account' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        if ($activity->max_pax && $validated['pax'] > $activity->max_pax) {
            throw ValidationException::withMessages([
                'pax' => "Maximum {$activity->max_pax} participants per booking.",
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

        $basePricePerPax = (float) $activity->price_per_pax;
        $tierData = app(PackTierService::class)->priceForPax($basePricePerPax, $validated['pax']);
        $effectivePricePerPax = $tierData['price_per_pax'];
        $tier = $tierData['tier'];

        $subtotal = round($effectivePricePerPax * $validated['pax'], 2);
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
        $dpPercent = (float) Setting::getValue('dp_percent_default', 30);
        $dpAmount = round($totalAmount * ($dpPercent / 100), 2);
        $remainingCash = round($totalAmount - $dpAmount, 2);

        $booking = ActivityBooking::create([
            'booking_code' => $this->generateUniqueBookingCode(),
            'activity_id' => $activity->id,
            'customer_id' => $customer->id,
            'booking_date' => $validated['booking_date'],
            'pax' => $validated['pax'],
            'price_per_pax' => $effectivePricePerPax,
            'total_price' => $totalAmount,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'voucher_code' => $voucher?->code,
            'dp_percent' => $dpPercent,
            'dp_amount' => $dpAmount,
            'remaining_cash' => $remainingCash,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'],
            'notes' => $validated['notes'] ?? null,
            'status' => ActivityBooking::STATUS_PENDING,
            'payment_status' => ActivityBooking::PAYMENT_PENDING,
        ]);

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
            $paymentUrl = $this->generateXenditPayment($booking, $activity);

            // Send payment reminder email
            if ($booking->customer_email) {
                try {
                    Mail::to($booking->customer_email)->send(new PaymentReminderMail($booking, $paymentUrl, true));
                    Log::info("Payment reminder email sent to {$booking->customer_email} for activity booking {$booking->booking_code}");
                } catch (\Exception $e) {
                    Log::error("Failed to send payment reminder email for activity booking {$booking->booking_code}: {$e->getMessage()}");
                }
            }

            app(WhatsAppService::class)->sendGroupMessage(
                "🏄 *New Activity Booking!*\n".
                "Code: {$booking->booking_code}\n".
                "Activity: {$activity->title}\n".
                "Date: {$booking->booking_date->format('d M Y')}\n".
                "Pax: {$booking->pax}\n".
                'Subtotal: Rp '.number_format((float) $booking->subtotal, 0, ',', '.')."\n".
                ($booking->discount_amount > 0 ? 'Discount: Rp '.number_format((float) $booking->discount_amount, 0, ',', '.')."\n" : '').
                'Total: Rp '.number_format((float) $booking->total_price, 0, ',', '.')."\n".
                'DP ('.$booking->dp_percent.'%): Rp '.number_format((float) $booking->dp_amount, 0, ',', '.')."\n".
                'Sisa tunai: Rp '.number_format((float) $booking->remaining_cash, 0, ',', '.')."\n".
                "Customer: {$booking->customer_name} ({$booking->customer_phone})"
            );

            return inertia()->location($paymentUrl);
        } catch (\Exception $e) {
            return redirect()->route('activities.show', $slug)
                ->with('error', 'Failed to create payment: '.$e->getMessage());
        }
    }

    private function generateXenditPayment(ActivityBooking $booking, Activity $activity): string
    {
        $xenditKey = Setting::getValue('xendit_secret_key') ?: config('services.xendit.secret_key');
        Configuration::setXenditKey($xenditKey);

        $guzzleClient = new Client(['verify' => ! app()->environment('local')]);
        $apiInstance = new InvoiceApi($guzzleClient);

        $successUrl = route('activities.booking.success', $booking->booking_code);
        $failureUrl = route('activities.show', $activity->slug).'?payment=failed';

        $req = new CreateInvoiceRequest([
            'external_id' => $booking->booking_code.'_'.time(),
            'amount' => (float) $booking->dp_amount,
            'payer_email' => $booking->customer_email,
            'description' => "Activity Booking DP ({$booking->dp_percent}%): {$activity->title} — {$booking->booking_code}",
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
        $activity = Activity::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'pax' => ['required', 'integer', 'min:1'],
            'email' => ['nullable', 'email'],
        ]);

        $basePricePerPax = (float) $activity->price_per_pax;
        $tierData = app(PackTierService::class)->priceForPax($basePricePerPax, $validated['pax']);
        $effectivePricePerPax = $tierData['price_per_pax'];

        $subtotal = round($effectivePricePerPax * $validated['pax'], 2);

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
            'price_per_pax' => $effectivePricePerPax,
            'tier_label' => $tierData['tier']?->label,
        ]);
    }

    public function success(string $bookingCode): Response
    {
        $booking = ActivityBooking::with('activity')
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/activity-booking-success', [
            'booking' => $booking,
        ]);
    }

    public function bookingDetail(string $bookingCode): Response
    {
        $booking = ActivityBooking::with('activity')
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/activity-booking-detail', [
            'booking' => $booking,
        ]);
    }

    private function generateUniqueBookingCode(): string
    {
        do {
            $code = 'ACT-'.strtoupper(Str::random(10));
        } while (ActivityBooking::where('booking_code', $code)->exists());

        return $code;
    }
}
