<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityBooking extends Model
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
        'activity_id',
        'customer_id',
        'booking_date',
        'pax',
        'price_per_pax',
        'total_price',
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
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
