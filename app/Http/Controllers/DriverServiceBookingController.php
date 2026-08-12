<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\Setting;
use App\Support\DriverReferralAttribution;
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

class DriverServiceBookingController extends Controller
{
    public function show(string $slug): Response
    {
        $service = DriverService::where('slug', $slug)->where('status', DriverService::STATUS_APPROVED)->firstOrFail();

        $customer = Auth::guard('customer')->user();

        return Inertia::render('customer/service-detail', [
            'service' => $service->load('driver'),
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

        $totalPrice = $service->price_per_pax * $validated['pax'];

        $booking = DriverServiceBooking::create([
            'booking_code' => $this->generateUniqueBookingCode(),
            'driver_service_id' => $service->id,
            'customer_id' => $customer->id,
            'booking_date' => $validated['booking_date'],
            'pax' => $validated['pax'],
            'price_per_pax' => $service->price_per_pax,
            'total_price' => $totalPrice,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'] ?? null,
            'customer_email' => $validated['customer_email'],
            'notes' => $validated['notes'] ?? null,
            'status' => DriverServiceBooking::STATUS_PENDING,
            'payment_status' => DriverServiceBooking::PAYMENT_PENDING,
        ]);

        DriverReferralAttribution::attributeService($booking);

        if ($request->boolean('create_account')) {
            Auth::guard('customer')->login($customer);
        }

        try {
            $paymentUrl = $this->generateXenditPayment($booking, $service);

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
            'amount' => (float) $booking->total_price,
            'payer_email' => $booking->customer_email,
            'description' => "Service Booking: {$service->title} — {$booking->booking_code}",
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
        $booking = DriverServiceBooking::with('driverService')
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return Inertia::render('customer/service-booking-success', [
            'booking' => $booking,
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
