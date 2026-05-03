<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'type',
        'photo_url',
        'latitude',
        'longitude',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
