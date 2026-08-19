<?php

namespace Database\Factories;

use App\Models\DriverWithdrawal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverWithdrawal>
 */
class DriverWithdrawalFactory extends Factory
{
    protected $model = DriverWithdrawal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => 250000,
            'bank_name' => 'BCA',
            'bank_account_number' => $this->faker->numerify('##########'),
            'bank_account_name' => $this->faker->name(),
            'status' => DriverWithdrawal::STATUS_PENDING,
        ];
    }
}
