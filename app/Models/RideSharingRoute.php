<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RideSharingRoute extends Model
{
    use HasFactory;

    protected $table = 'rs_routes';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function paths(): HasMany
    {
        return $this->hasMany(RideSharingRoutePath::class, 'route_id')->orderBy('sequence', 'asc');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RideSharingRoutePrice::class, 'route_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(RideSharingSchedule::class, 'route_id');
    }
}
