<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscrowAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'total_amount',
        'platform_fee',
        'freelancer_amount',
        'status',
        'released_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'freelancer_amount' => 'decimal:2',
        'released_at' => 'datetime',
    ];

    /**
     * Get the booking this escrow account belongs to
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
