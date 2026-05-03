<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'order_number',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'date',
        'time',
        'flight_number',
        'notes',
        'driver_id',
        'claimed_driver_id',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'passengers',
        'price',
        'parking_gas_fee',
        'status',
        'is_cash',
        'is_shared',
        'is_cancelled',
        'vehicle_id',
    ];

    protected $appends = [
        'has_claims',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'parking_gas_fee' => 'decimal:2',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
        'is_cash' => 'boolean',
        'is_shared' => 'boolean',
        'is_cancelled' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getHasClaimsAttribute(): bool
    {
        return $this->claims()->where('status', 'pending')->exists();
    }

    public function currentClaim(): HasOne
    {
        return $this->hasOne(JobClaim::class)->where('status', 'pending')->latestOfMany();
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function claimedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'claimed_driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(OrderEvidence::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(JobClaim::class);
    }
}
