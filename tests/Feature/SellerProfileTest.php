<?php

namespace Tests\Feature;

use App\Models\SellerProfile;
use App\Models\User;
use Tests\TestCase;

class SellerProfileTest extends TestCase
{
    private User $seller;
    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
    }

    /**
     * Test create seller profile with valid data
     */
    public function test_create_seller_profile_with_valid_data(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 50.00,
                'experience_level' => 'intermediate',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Seller profile created successfully',
            ]);

        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);
    }

    /**
     * Test create seller profile as beginner
     */
    public function test_create_seller_profile_as_beginner(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 25.00,
                'experience_level' => 'beginner',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $this->seller->id,
            'experience_level' => 'beginner',
        ]);
    }

    /**
     * Test create seller profile as expert
     */
    public function test_create_seller_profile_as_expert(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 150.00,
                'experience_level' => 'expert',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('seller_profiles', [
            'experience_level' => 'expert',
        ]);
    }

    /**
     * Test cannot create seller profile as buyer
     */
    public function test_buyer_cannot_create_seller_profile(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 50.00,
                'experience_level' => 'intermediate',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test validation error for negative hourly rate
     */
    public function test_validation_error_negative_hourly_rate(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => -10.00,
                'experience_level' => 'intermediate',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hourly_rate');
    }

    /**
     * Test validation error for invalid experience level
     */
    public function test_validation_error_invalid_experience_level(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 50.00,
                'experience_level' => 'invalid_level',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('experience_level');
    }

    /**
     * Test validation error for missing hourly rate
     */
    public function test_validation_error_missing_hourly_rate(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'experience_level' => 'intermediate',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('hourly_rate');
    }

    /**
     * Test validation error for missing experience level
     */
    public function test_validation_error_missing_experience_level(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 50.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('experience_level');
    }

    /**
     * Test get own seller profile
     */
    public function test_get_own_seller_profile(): void
    {
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson('/api/profile/seller');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user_id' => $this->seller->id,
                    'hourly_rate' => '50.00',
                    'experience_level' => 'intermediate',
                ],
            ]);
    }

    /**
     * Test get seller profile when not exists
     */
    public function test_get_seller_profile_when_not_exists(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson('/api/profile/seller');

        $response->assertStatus(404);
    }

    /**
     * Test buyer cannot get seller profile endpoint
     */
    public function test_buyer_cannot_get_seller_profile_endpoint(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/profile/seller');

        $response->assertStatus(403);
    }

    /**
     * Test update seller profile
     */
    public function test_update_seller_profile(): void
    {
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson('/api/profile/seller', [
                'hourly_rate' => 75.00,
                'experience_level' => 'expert',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Seller profile updated successfully',
            ]);

        $this->assertDatabaseHas('seller_profiles', [
            'user_id' => $this->seller->id,
            'hourly_rate' => 75.00,
            'experience_level' => 'expert',
        ]);
    }

    /**
     * Test update only hourly rate
     */
    public function test_update_only_hourly_rate(): void
    {
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson('/api/profile/seller', [
                'hourly_rate' => 100.00,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('seller_profiles', [
            'hourly_rate' => 100.00,
            'experience_level' => 'intermediate',
        ]);
    }

    /**
     * Test update only experience level
     */
    public function test_update_only_experience_level(): void
    {
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'beginner',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson('/api/profile/seller', [
                'experience_level' => 'expert',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('seller_profiles', [
            'hourly_rate' => 50.00,
            'experience_level' => 'expert',
        ]);
    }

    /**
     * Test cannot update profile as buyer
     */
    public function test_buyer_cannot_update_seller_profile(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->putJson('/api/profile/seller', [
                'hourly_rate' => 75.00,
                'experience_level' => 'expert',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test get public seller profile by ID
     */
    public function test_get_public_seller_profile_by_id(): void
    {
        $profile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response = $this->getJson("/api/sellers/{$profile->id}/profile");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $profile->id,
                    'hourly_rate' => '50.00',
                    'experience_level' => 'intermediate',
                ],
            ]);
    }

    /**
     * Test get public seller profile not found
     */
    public function test_get_public_seller_profile_not_found(): void
    {
        $response = $this->getJson('/api/sellers/9999/profile');

        $response->assertStatus(404);
    }

    /**
     * Test public can access seller profile
     */
    public function test_unauthenticated_can_get_public_seller_profile(): void
    {
        $profile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response = $this->getJson("/api/sellers/{$profile->id}/profile");

        $response->assertStatus(200);
    }

    /**
     * Test unauthorized access to profile endpoints
     */
    public function test_unauthorized_access_to_create_profile(): void
    {
        $response = $this->postJson('/api/profile/seller', [
            'hourly_rate' => 50.00,
            'experience_level' => 'intermediate',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to get profile
     */
    public function test_unauthorized_access_to_get_profile(): void
    {
        $response = $this->getJson('/api/profile/seller');

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to update profile
     */
    public function test_unauthorized_access_to_update_profile(): void
    {
        $response = $this->putJson('/api/profile/seller', [
            'hourly_rate' => 75.00,
            'experience_level' => 'expert',
        ]);

        $response->assertStatus(401);
    }
}
