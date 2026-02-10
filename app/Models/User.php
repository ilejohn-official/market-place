<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    use \Laravel\Sanctum\HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Check if user is a buyer
     */
    public function isBuyer(): bool
    {
        return $this->role === 'buyer';
    }

    /**
     * Check if user is a seller
     */
    public function isSeller(): bool
    {
        return $this->role === 'seller';
    }

    /**
     * Get the seller profile for this user (if seller)
     */
    public function sellerProfile(): HasOne
    {
        return $this->hasOne(SellerProfile::class);
    }

    /**
     * Get all services created by this user (if seller)
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'seller_id');
    }

    /**
     * Get all bookings where this user is buyer
     */
    public function buyerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'buyer_id');
    }

    /**
     * Get all bookings where this user is seller
     */
    public function sellerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'seller_id');
    }

    /**
     * Get the wallet for this user
     */
    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Get transactions for this user
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
