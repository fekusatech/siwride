<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverWalletTransaction extends Model
{
    use HasFactory;

    public const TYPE_DP_PAYMENT = 'dp_payment';

    public const TYPE_DP_REVERSAL = 'dp_reversal';

    public const TYPE_PLATFORM_COMMISSION = 'platform_commission';

    public const TYPE_WITHDRAWAL = 'withdrawal';

    public const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'balance_after',
        'booking_code',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(DriverWallet::class, 'wallet_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
