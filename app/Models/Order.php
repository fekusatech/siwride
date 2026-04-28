<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'order_number',
        'customer_id',
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
        'vehicle_id',
    ];

    protected $casts = [
        'date' => 'date',
        'price' => 'decimal:2',
        'parking_gas_fee' => 'decimal:2',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function claimedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'claimed_driver_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
