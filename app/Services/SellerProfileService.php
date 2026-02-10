<?php

namespace App\Services;

use App\Models\SellerProfile;
use App\Models\User;
use Exception;

class SellerProfileService
{
    /**
     * Create or update seller profile
     */
    public function createOrUpdateProfile(User $user, array $data): SellerProfile
    {
        if (!$user->isSeller()) {
            throw new Exception('Only sellers can create a profile');
        }

        $profile = SellerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'hourly_rate' => $data['hourly_rate'],
                'experience_level' => $data['experience_level'],
            ]
        );

        return $profile;
    }

    /**
     * Get seller profile by user
     */
    public function getProfileByUser(User $user): ?SellerProfile
    {
        if (!$user->isSeller()) {
            throw new Exception('Only sellers have profiles');
        }

        return $user->sellerProfile;
    }

    /**
     * Get seller profile by ID
     */
    public function getProfileById(int $profileId): ?SellerProfile
    {
        return SellerProfile::find($profileId);
    }

    /**
     * Update seller profile
     */
    public function updateProfile(User $user, array $data): SellerProfile
    {
        if (!$user->isSeller()) {
            throw new Exception('Only sellers can update a profile');
        }

        $profile = $user->sellerProfile;

        if (!$profile) {
            throw new Exception('Seller profile not found');
        }

        $profile->update([
            'hourly_rate' => $data['hourly_rate'] ?? $profile->hourly_rate,
            'experience_level' => $data['experience_level'] ?? $profile->experience_level,
        ]);

        return $profile;
    }

    /**
     * Calculate average rating for a seller
     */
    public function calculateAverageRating(User $seller): float
    {
        if (!$seller->isSeller()) {
            return 0;
        }

        $profile = $seller->sellerProfile;
        if (!$profile) {
            return 0;
        }

        return $profile->rating ?? 0;
    }
}
