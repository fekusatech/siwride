<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverReferral extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT = 'pending_payment';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'driver_id',
        'driver_service_id',
        'booking_key',
        'order_id',
        'activity_booking_id',
        'driver_service_booking_id',
        'commission_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'commission_amount' => 'decimal:2',
        ];
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(DriverService::class, 'driver_service_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function activityBooking(): BelongsTo
    {
        return $this->belongsTo(ActivityBooking::class);
    }

    public function serviceBooking(): BelongsTo
    {
        return $this->belongsTo(DriverServiceBooking::class, 'driver_service_booking_id');
    }
}
