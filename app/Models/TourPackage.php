<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'highlights',
        'price_per_pax',
        'duration_hours',
        'max_pax',
        'min_pax',
        'destinations',
        'itinerary',
        'includes',
        'excludes',
        'image',
        'gallery',
        'is_active',
        'sort_order',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'destinations' => 'array',
            'itinerary' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'gallery' => 'array',
            'is_active' => 'boolean',
            'price_per_pax' => 'integer',
            'duration_hours' => 'integer',
            'max_pax' => 'integer',
            'min_pax' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('assets/images/services/tour-default.jpg');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://') || str_starts_with($this->image, '/')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'assets/')) {
            return asset($this->image);
        }

        return asset('storage/'.$this->image);
    }
}
