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
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['uid', 'firstname', 'lastname', 'email', 'phone', 'password', 'image', 'status', 'role', 'nid', 'nik', 'nik_image', 'sim', 'sim_image'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $appends = ['driver_id', 'vehicle'];

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
    public function name(): string
    {
        return "{$this->firstname} {$this->lastname}";
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
