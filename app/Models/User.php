<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['uid', 'firstname', 'lastname', 'email', 'phone', 'password', 'image', 'status', 'role', 'nid', 'nik', 'nik_image', 'sim', 'sim_image', 'slug'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $appends = ['name', 'driver_id', 'vehicle'];

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class, 'email', 'email');
    }

    public function getDriverIdAttribute()
    {
        return $this->driver?->id;
    }

    public function getVehicleAttribute()
    {
        return $this->driver?->vehicles()->first();
    }

    public function ordersAsDriver(): HasMany
    {
        return $this->hasMany(Order::class, 'driver_id');
    }

    public function driverLocation(): HasOne
    {
        return $this->hasOne(DriverLocation::class, 'driver_id');
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * Generate and persist a unique URL slug for public driver profiles.
     */
    public function ensureDriverSlug(): string
    {
        if ($this->slug) {
            return $this->slug;
        }

        $base = Str::slug($this->name) ?: 'driver';
        $slug = $base;
        $counter = 2;

        while (static::where('slug', $slug)->whereKeyNot($this->getKey())->exists()) {
            $slug = $base.'-'.$counter++;
        }

        $this->update(['slug' => $slug]);

        return $slug;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
