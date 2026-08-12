<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverService extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'driver_id',
        'title',
        'slug',
        'price_per_pax',
        'min_pax',
        'max_pax',
        'duration_label',
        'meeting_point',
        'description',
        'includes',
        'excludes',
        'highlights',
        'image',
        'gallery',
        'status',
        'rejection_reason',
        'published_at',
    ];

    protected $appends = ['image_url', 'gallery_urls'];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'includes' => 'array',
            'excludes' => 'array',
            'highlights' => 'array',
            'price_per_pax' => 'decimal:2',
            'published_at' => 'datetime',
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

    private function resolveUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return asset('storage/'.$path);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(DriverServiceBooking::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(DriverReferral::class);
    }
}
