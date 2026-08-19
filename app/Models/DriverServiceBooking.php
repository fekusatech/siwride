<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverServiceBooking extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_COMPLETED = 'completed';

    const PAYMENT_PENDING = 'pending';

    const PAYMENT_PAID = 'paid';

    const PAYMENT_FAILED = 'failed';

    const PAYMENT_EXPIRED = 'expired';

    protected $fillable = [
        'booking_code',
        'driver_service_id',
        'assigned_driver_id',
        'assignment_status',
        'accepted_at',
        'customer_id',
        'booking_date',
        'pax',
        'price_per_pax',
        'total_price',
        'subtotal',
        'discount_amount',
        'voucher_code',
        'total_amount',
        'dp_percent',
        'dp_amount',
        'remaining_cash',
        'cash_confirmed_at',
        'customer_name',
        'customer_phone',
        'customer_email',
        'notes',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'price_per_pax' => 'decimal:2',
            'total_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'dp_percent' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'remaining_cash' => 'decimal:2',
            'accepted_at' => 'datetime',
            'cash_confirmed_at' => 'datetime',
        ];
    }

    public function driverService(): BelongsTo
    {
        return $this->belongsTo(DriverService::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_driver_id');
    }
}
