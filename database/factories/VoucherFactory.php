<?php

namespace Database\Factories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Voucher>
 */
class VoucherFactory extends Factory
{
    protected $model = Voucher::class;

    public function definition(): array
    {
        return [
            'code' => 'VC-'.strtoupper(Str::random(8)),
            'type' => Voucher::TYPE_PERCENT,
            'value' => 10,
            'min_spend' => 0,
            'max_discount' => null,
            'usage_limit' => null,
            'usage_limit_per_user' => null,
            'used_count' => 0,
            'valid_from' => null,
            'valid_until' => null,
            'applies_to' => null,
            'is_active' => true,
        ];
    }
}
