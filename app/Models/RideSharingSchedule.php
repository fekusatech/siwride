<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RideSharingSchedule extends Model
{
    use HasFactory;

    protected $table = 'rs_schedules';

    protected $fillable = [
        'route_id',
        'vehicle_category_id',
        'days',
        'departure_times',
        'quota',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'days' => 'array',
        'departure_times' => 'array',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(RideSharingRoute::class, 'route_id');
    }

    public function vehicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RideSharingRoutePrice::class, 'schedule_id');
    }
}
