<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\ActivityBooking;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityBooking>
 */
class ActivityBookingFactory extends Factory
{
    protected $model = ActivityBooking::class;

    public function definition(): array
    {
        return [
            'booking_code' => fake()->unique()->regexify('ACT-[A-Z0-9]{10}'),
            'activity_id' => Activity::factory(),
            'customer_id' => Customer::factory(),
            'booking_date' => now()->addDay()->toDateString(),
            'pax' => 2,
            'price_per_pax' => 150000,
            'total_price' => 300000,
            'subtotal' => 300000,
            'discount_amount' => 0,
            'dp_percent' => 30,
            'dp_amount' => 90000,
            'remaining_cash' => 210000,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->unique()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'notes' => null,
            'status' => ActivityBooking::STATUS_PENDING,
            'payment_status' => ActivityBooking::PAYMENT_PENDING,
        ];
    }
}
