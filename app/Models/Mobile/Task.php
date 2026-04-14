<?php

namespace App\Models\Mobile;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $table = 'mobile_tasks';

    protected $fillable = [
        'kode_booking',
        'no',
        'tanggal',
        'jam',
        'nama_tamu',
        'phone_tamu',
        'flight',
        'pickup',
        'dropoff',
        'pickup_lat',
        'pickup_lng',
        'dropoff_lat',
        'dropoff_lng',
        'pass',
        'price',
        'status',
        'driver_id',
        'driver_name',
        'admin_id',
        'is_shared',
        'is_cash',
        'is_cancelled',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'is_cash' => 'boolean',
        'is_cancelled' => 'boolean',
        'price' => 'integer',
        'pass' => 'integer',
        'pickup_lat' => 'decimal:8',
        'pickup_lng' => 'decimal:8',
        'dropoff_lat' => 'decimal:8',
        'dropoff_lng' => 'decimal:8',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(TaskCheckin::class);
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(TaskProof::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByDriver(Builder $query, int $driverId): Builder
    {
        return $query->where('driver_id', $driverId);
    }

    public function scopeByDateRange(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('tanggal', [$from, $to]);
    }
}
