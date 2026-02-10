<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SellerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'hourly_rate',
        'rating',
        'experience_level',
        'total_reviews',
        'completion_rate',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'completion_rate' => 'decimal:2',
    ];

    /**
     * Get the user that owns this seller profile
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all services for this seller
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'seller_id', 'user_id');
    }
}
