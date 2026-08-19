<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DriverService;
use App\Models\DriverServiceBooking;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverServiceBooking>
 */
class DriverServiceBookingFactory extends Factory
{
    protected $model = DriverServiceBooking::class;

    public function definition(): array
    {
        return [
            'booking_code' => fake()->unique()->regexify('SVC-[A-Z0-9]{10}'),
            'driver_service_id' => DriverService::factory(),
            'customer_id' => Customer::factory(),
            'booking_date' => now()->addDay()->toDateString(),
            'pax' => 2,
            'price_per_pax' => 100000,
            'total_price' => 200000,
            'subtotal' => 200000,
            'discount_amount' => 0,
            'total_amount' => 200000,
            'dp_percent' => 30,
            'dp_amount' => 60000,
            'remaining_cash' => 140000,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->unique()->safeEmail(),
            'status' => DriverServiceBooking::STATUS_PENDING,
            'payment_status' => DriverServiceBooking::PAYMENT_PENDING,
        ];
    }
}
