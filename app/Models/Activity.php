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
        'href',
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

    protected $appends = ['image_url', 'gallery_urls', 'link_url'];

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

    private const PLACEHOLDER_IMAGE = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23e9ecef'/%3E%3Cpath d='M160 120a20 20 0 1 1 0-40 20 20 0 0 1 0 40Zm-40 100 60-70 40 45 30-35 70 60H120Z' fill='%23adb5bd'/%3E%3C/svg%3E";

    public function getImageUrlAttribute(): string
    {
        return $this->resolveUrl($this->image) ?? self::PLACEHOLDER_IMAGE;
    }

    public function getGalleryUrlsAttribute(): array
    {
        return collect($this->gallery ?? [])
            ->map(fn (string $path) => $this->resolveUrl($path))
            ->all();
    }

    public function getLinkUrlAttribute(): string
    {
        return $this->href ?: "/activities/{$this->slug}";
    }

    private function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'assets/')) {
            return asset($path);
        }

        return asset('storage/'.$path);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ActivityBooking::class);
    }
}
