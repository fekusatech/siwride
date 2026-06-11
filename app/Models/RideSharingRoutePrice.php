<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideSharingRoutePrice extends Model
{
    use HasFactory;

    protected $table = 'rs_route_prices';

    protected $fillable = [
        'route_id',
        'schedule_id',
        'from_city_id',
        'to_city_id',
        'price',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(RideSharingRoute::class, 'route_id');
    }

    public function fromCity(): BelongsTo
    {
        return $this->belongsTo(RideSharingCity::class, 'from_city_id');
    }

    public function toCity(): BelongsTo
    {
        return $this->belongsTo(RideSharingCity::class, 'to_city_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(RideSharingSchedule::class, 'schedule_id');
    }
}
