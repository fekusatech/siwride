<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RideSharingCity extends Model
{
    use HasFactory;

    protected $table = 'rs_cities';

    protected $fillable = [
        'name',
        'address',
    ];

    public function routePaths(): HasMany
    {
        return $this->hasMany(RideSharingRoutePath::class, 'city_id');
    }
}
