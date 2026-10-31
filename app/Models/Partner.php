<?php

namespace App\Models;

use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Partner extends Model
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    public const TYPE_TRAVEL = 'travel';

    public const TYPE_GUIDE = 'guide';

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'password',
        'slug',
        'description',
        'image',
        'nik',
        'nik_image',
        'status',
        'approved_at',
    ];

    protected $hidden = [
        'nik',
        'nik_image',
        'password',
        'remember_token',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'approved_at' => 'datetime',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeType($query, ?string $type)
    {
        return $query->when($type, fn ($q) => $q->where('type', $type));
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function approve(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'approved_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_at' => null,
        ]);
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === self::TYPE_GUIDE ? 'Tour Guide' : 'Travel Partner';
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

    /**
     * Generate and persist a unique URL slug for public partner profiles.
     */
    public function ensureUniqueSlug(): string
    {
        if ($this->slug) {
            return $this->slug;
        }

        $base = Str::slug($this->name) ?: 'partner';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = $base.'-'.$counter++;
        }

        $this->update(['slug' => $slug]);

        return $slug;
    }
}
