<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'subtitle',
        'description',
        'image',
        'gallery',
        'price_per_pax',
        'min_pax',
        'max_pax',
        'duration_label',
        'meeting_point',
        'includes',
        'excludes',
        'highlights',
        'is_active',
        'sort_order',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'highlights' => 'array',
            'price_per_pax' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('assets/images/services/default.png');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://') || str_starts_with($this->image, '/')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ActivityBooking::class);
    }
}
