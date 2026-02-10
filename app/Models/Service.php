<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'title',
        'description',
        'category',
        'price',
        'estimated_days',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Get the seller that owns this service
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get all bookings for this service
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
