<?php

namespace App\Models;

use Database\Factories\RideSharingScheduleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RideSharingSchedule extends Model
{
    /** @use HasFactory<RideSharingScheduleFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = ['departure_time', 'label', 'is_active', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Scope to only return active schedules ordered by sort_order. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
