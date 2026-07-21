<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Customer;
use App\Models\Setting;
use App\Services\WhatsAppService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        return Inertia::render('customer/activity-detail', [
            'activity' => $activity,
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

        $totalPrice = $activity->price_per_pax * $validated['pax'];

        $booking = ActivityBooking::create([
            'booking_code' => $this->generateUniqueBookingCode(),
            'activity_id' => $activity->id,
            'customer_id' => $customer->id,
            'booking_date' => $validated['booking_date'],
            'pax' => $validated['pax'],
            'price_per_pax' => $activity->price_per_pax,
            'total_price' => $totalPrice,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'],
            'notes' => $validated['notes'] ?? null,
            'status' => ActivityBooking::STATUS_PENDING,
            'payment_status' => ActivityBooking::PAYMENT_PENDING,
        ]);

        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        try {
            $paymentUrl = $this->generateXenditPayment($booking, $activity);

            app(WhatsAppService::class)->sendGroupMessage(
                "🏄 *New Activity Booking!*\n".
                "Code: {$booking->booking_code}\n".
                "Activity: {$activity->title}\n".
                "Date: {$booking->booking_date->format('d M Y')}\n".
                "Pax: {$booking->pax}\n".
                'Total: Rp '.number_format((float) $booking->total_price, 0, ',', '.')."\n".
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
            'amount' => (float) $booking->total_price,
            'payer_email' => $booking->customer_email,
            'description' => "Activity Booking: {$activity->title} — {$booking->booking_code}",
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

    public function success(string $bookingCode): Response
    {
        $booking = ActivityBooking::with('activity')
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/activity-booking-success', [
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
