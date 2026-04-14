<?php

namespace App\Models\Mobile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskProof extends Model
{
    use HasFactory;

    protected $table = 'mobile_task_proofs';

    protected $fillable = [
        'task_id',
        'type',
        'file_url',
        'latitude',
        'longitude',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
