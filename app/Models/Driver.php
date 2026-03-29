<?php

namespace App\Models;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nid', 'name', 'email', 'phone', 'password', 'image', 'status', 'nik', 'nik_image', 'sim', 'sim_image'])]
#[Hidden(['password', 'remember_token'])]
class Driver extends Authenticatable
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory, Notifiable;

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
