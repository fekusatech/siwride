<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackTier extends Model
{
    use HasFactory;

    public const TYPE_PERCENT = 'percent';

    public const TYPE_FLAT = 'flat';

    protected $fillable = [
        'label',
        'min_pax',
        'max_pax',
        'discount_type',
        'discount_value',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_pax' => 'integer',
            'max_pax' => 'integer',
            'discount_value' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Check if a given pax count falls within this tier's range.
     */
    public function matchesPax(int $pax): bool
    {
        if ($pax < $this->min_pax) {
            return false;
        }

        return $this->max_pax === null || $pax <= $this->max_pax;
    }

    /**
     * Compute the effective price per pax given a base price.
     */
    public function pricePerPax(float $basePrice): float
    {
        return match ($this->discount_type) {
            self::TYPE_PERCENT => round($basePrice * (1 - (float) $this->discount_value / 100), 2),
            self::TYPE_FLAT => round(max(0, $basePrice - (float) $this->discount_value), 2),
            default => $basePrice,
        };
    }

    /**
     * Describe the discount for display.
     */
    public function discountLabel(): string
    {
        return match ($this->discount_type) {
            self::TYPE_PERCENT => (rtrim(rtrim((string) $this->discount_value, '0'), '.').'% off'),
            self::TYPE_FLAT => 'Rp '.number_format((float) $this->discount_value, 0, ',', '.').' off / pax',
            default => '',
        };
    }
}
