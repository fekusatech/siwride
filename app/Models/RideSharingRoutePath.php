<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RideSharingRoutePath extends Model
{
    use HasFactory;

    protected $table = 'rs_route_paths';

    protected $fillable = [
        'route_id',
        'city_id',
        'sequence',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(RideSharingRoute::class, 'route_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(RideSharingCity::class, 'city_id');
    }
}
