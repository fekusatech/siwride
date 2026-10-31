<?php

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Setting;
use App\Models\User;
use App\Models\VehicleCategory;
use App\Models\Zone;
use App\Models\ZonePricingRule;
use App\Services\BookingExtrasService;
use App\Services\CustomerOrderPaymentService;
use App\Services\CustomerTransferBookingService;
use App\Services\CustomerTransferPricingService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

function auditBookingData(): array
{
    $zone = Zone::create(['name' => 'Audit Zone', 'is_active' => true, 'coordinates' => [
        ['lat' => 0, 'lng' => 102], ['lat' => 0, 'lng' => 104], ['lat' => 2, 'lng' => 104], ['lat' => 2, 'lng' => 102],
    ]]);
    $vehicle = VehicleCategory::create(['slug' => 'audit-car', 'title' => 'Audit car', 'vehicle_type' => 'sedan', 'base_price' => 200000, 'price_per_km' => 0]);
    ZonePricingRule::create(['pickup_zone_id' => $zone->id, 'dropoff_zone_id' => $zone->id, 'is_active' => true, 'minimum_price' => 200000]);

    return ['customer_name' => 'Booking Guest', 'email' => 'guest@example.com', 'customer_phone' => '08123456789',
        'pickup_address' => 'Airport terminal', 'dropoff_address' => 'Hotel destination', 'pickup_latitude' => 1, 'pickup_longitude' => 103,
        'dropoff_latitude' => 1.1, 'dropoff_longitude' => 103.1, 'vehicle_category_id' => $vehicle->id,
        'date' => now()->addDays(3)->toDateString(), 'time' => '10:00', 'passengers' => 2, 'trip_type' => 'one_way'];
}

it('requires category and coordinates for transfer bookings', function () {
    $this->postJson(route('orders.store'), [])->assertUnprocessable()->assertJsonValidationErrors([
        'vehicle_category_id', 'pickup_latitude', 'pickup_longitude', 'dropoff_latitude', 'dropoff_longitude',
    ]);
});

it('prices extras from the server and preserves the existing customer profile', function () {
    Mail::fake();
    $data = auditBookingData();
    $customer = Customer::factory()->create(['email' => $data['email'], 'name' => 'Original Name', 'phone' => 'original-phone']);
    $this->mock(CustomerOrderPaymentService::class)->shouldReceive('createInvoice')->once()->andReturn('/booking/example');
    $data['extras'] = [['id' => 'water', 'label' => 'Spoofed label', 'price' => 0]];
    $this->post(route('orders.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
    $order = Order::firstOrFail();
    expect($order->customer_name)->toBe('Booking Guest')->and($order->customer_phone)->toBe($data['customer_phone'])
        ->and($order->extras[0])->toBe(['id' => 'water', 'label' => 'Drinking water', 'price' => 20000])
        ->and((float) $order->price)->toBe(220000.0)
        ->and($customer->fresh()->name)->toBe('Original Name')->and($customer->fresh()->phone)->toBe('original-phone');
});

it('preserves existing customers in native transfer bookings', function () {
    $data = auditBookingData();
    $customer = Customer::factory()->create(['email' => $data['email'], 'name' => 'Original', 'phone' => 'original']);
    $order = app(CustomerTransferBookingService::class)->create($data);
    expect($customer->fresh()->name)->toBe('Original')->and($customer->fresh()->phone)->toBe('original')
        ->and($order->customer_name)->toBe($data['customer_name']);
});

it('rejects zero calculated fares on web and native bookings', function () {
    $data = auditBookingData();
    VehicleCategory::query()->update(['base_price' => 0, 'price_per_km' => 0]);
    ZonePricingRule::query()->update(['minimum_price' => 0]);
    $this->postJson(route('orders.store'), $data)->assertUnprocessable()->assertJsonValidationErrors('vehicle_category_id');
    expect(Order::count())->toBe(0);
    expect(fn () => app(CustomerTransferPricingService::class)->priceForVehicle(['prices' => collect([['id' => 1, 'estimated_price' => 0]])], 1))
        ->toThrow(ValidationException::class);
});

it('rejects unknown and repeated extras', function (array $selected) {
    expect(fn () => app(BookingExtrasService::class)->resolve($selected))->toThrow(ValidationException::class);
})->with([
    'unknown' => [[['id' => 'missing']]],
    'duplicate' => [[['id' => 'water'], ['id' => 'water']]],
]);

it('inventories setting keys and legacy files without leaking values or deleting files', function () {
    Storage::fake('public');
    Storage::disk('public')->put('partners/nik/legacy.jpg', 'identity');
    Storage::disk('public')->put('legacy-identity.jpg', 'identity');
    Partner::factory()->create(['nik_image' => 'legacy-identity.jpg']);
    Setting::setValue('xendit_secret_key', 'do-not-print');
    $this->artisan('audit:exposure-inventory', ['--cleanup-sql' => true])
        ->expectsOutput('xendit_secret_key')->expectsOutput('partners/nik/legacy.jpg')->expectsOutput('legacy-identity.jpg')
        ->doesntExpectOutput('do-not-print')->assertSuccessful();
    Storage::disk('public')->assertExists('partners/nik/legacy.jpg');
    expect(Setting::getValue('xendit_secret_key'))->toBe('do-not-print');
});

it('preserves profiles for activity and driver service booking guests', function (string $kind) {
    Mail::fake();
    $customer = Customer::factory()->create(['name' => 'Original Name', 'phone' => 'original-phone']);
    if ($kind === 'activity') {
        $item = Activity::factory()->create(['min_pax' => 1]);
        $url = route('activities.book', $item->slug);
    } else {
        $item = DriverService::factory()->create(['min_pax' => 1]);
        $url = route('driver-services.book', $item->slug);
    }
    $this->post($url, [
        'booking_date' => now()->addDays(3)->toDateString(), 'pax' => 2,
        'customer_email' => $customer->email, 'customer_name' => 'Booking Snapshot', 'customer_phone' => '081234567890',
    ])->assertSessionHasNoErrors();
    $booking = $kind === 'activity' ? ActivityBooking::firstOrFail() : DriverServiceBooking::firstOrFail();
    expect($booking->customer_name)->toBe('Booking Snapshot')->and($booking->customer_phone)->toBe('081234567890')
        ->and($customer->fresh()->name)->toBe('Original Name')->and($customer->fresh()->phone)->toBe('original-phone');
})->with(['activity', 'service']);

it('keeps customer profiles unchanged when admins create or edit orders', function () {
    $customer = Customer::factory()->create(['name' => 'Original Name', 'phone' => 'original-phone']);
    $admin = User::factory()->create(['role' => 'admin']);
    $data = [
        'booking_code' => 'SWAUDIT', 'order_number' => 'AUDIT-1', 'date' => now()->addDay()->toDateString(), 'time' => '10:00',
        'customer_name' => 'Booking Snapshot', 'customer_phone' => '081234567890', 'customer_email' => $customer->email,
        'pickup_address' => 'Airport', 'dropoff_address' => 'Hotel', 'passengers' => 2, 'price' => 200000, 'parking_gas_fee' => 0,
    ];
    $this->actingAs($admin)->post(route('admin.orders.store'), $data)->assertSessionHasNoErrors();
    $order = Order::firstOrFail();
    expect($order->posted_by)->toBe($admin->id)->and($order->customer_name)->toBe('Booking Snapshot');
    $data['customer_name'] = 'Updated Snapshot';
    $data['status'] = 'pending';
    $this->put(route('admin.orders.update', $order), $data)->assertSessionHasNoErrors();
    expect($order->fresh()->customer_name)->toBe('Updated Snapshot')
        ->and($customer->fresh()->name)->toBe('Original Name')->and($customer->fresh()->phone)->toBe('original-phone');
});
