<?php

namespace Database\Factories;

use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'type' => fake()->randomElement([Partner::TYPE_TRAVEL, Partner::TYPE_GUIDE]),
            'name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('08##########'),
            'password' => bcrypt('password'),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(2),
            'image' => null,
            'nik' => fake()->numerify('################'),
            'nik_image' => null,
            'status' => Partner::STATUS_PENDING,
            'approved_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => Partner::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);
    }

    public function travel(): static
    {
        return $this->state(fn () => [
            'type' => Partner::TYPE_TRAVEL,
        ]);
    }

    public function guide(): static
    {
        return $this->state(fn () => [
            'type' => Partner::TYPE_GUIDE,
        ]);
    }
}
