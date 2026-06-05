<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_code' => strtoupper(Str::random(10)),
            'order_number' => 'ORD-'.fake()->numerify('#####'),
            'date' => now()->toDateString(),
            'time' => now()->addHour()->format('H:i:s'),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->e164PhoneNumber(),
            'customer_email' => fake()->safeEmail(),
            'flight_number' => fake()->optional()->bothify('??####'),
            'pickup_address' => fake()->address(),
            'pickup_latitude' => fake()->latitude(),
            'pickup_longitude' => fake()->longitude(),
            'dropoff_address' => fake()->address(),
            'dropoff_latitude' => fake()->latitude(),
            'dropoff_longitude' => fake()->longitude(),
            'passengers' => $this->faker->numberBetween(1, 4),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'parking_gas_fee' => 0,
            'status' => 'pending',
            'is_cash' => false,
            'is_shared' => false,
            'is_cancelled' => false,
            'payment_method' => null,
            'payment_reference' => null,
            'payment_status' => 'pending',
            'payment_expiry' => null,
        ];
    }
}
