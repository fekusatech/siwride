<?php

namespace App\Models\Mobile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'mobile_users';

    protected $fillable = [
        'uid',
        'email',
        'display_name',
        'phone_number',
        'role',
        'status',
        'photo_url',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tasksAsDriver(): HasMany
    {
        return $this->hasMany(Task::class, 'driver_id');
    }

    public function tasksAsAdmin(): HasMany
    {
        return $this->hasMany(Task::class, 'admin_id');
    }

    public function vehicle(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'driver_id');
    }

    public function driverLocation(): HasOne
    {
        return $this->hasOne(DriverLocation::class, 'driver_id');
    }
}
