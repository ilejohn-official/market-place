<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'buyer_id',
        'seller_id',
        'service_id',
        'status',
        'agreed_amount',
        'negotiation_notes',
        'start_date',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'agreed_amount' => 'decimal:2',
        'completed_at' => 'datetime',
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    /**
     * Get the buyer for this booking
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller for this booking
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the service for this booking
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the escrow account for this booking
     */
    public function escrowAccount(): HasOne
    {
        return $this->hasOne(EscrowAccount::class);
    }
}
