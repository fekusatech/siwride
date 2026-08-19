<?php

namespace Database\Factories;

use App\Models\DriverWallet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DriverWallet>
 */
class DriverWalletFactory extends Factory
{
    protected $model = DriverWallet::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'balance' => 0,
            'is_negative' => false,
        ];
    }
}
