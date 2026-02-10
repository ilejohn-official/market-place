<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SellerProfileTest extends TestCase
{
    public function test_seller_profile_create_and_get(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $createResponse = $this->actingAs($seller, 'sanctum')
            ->postJson('/api/profile/seller', [
                'hourly_rate' => 50,
                'experience_level' => 'intermediate',
            ]);

        $createResponse->assertStatus(201);

        $getResponse = $this->actingAs($seller, 'sanctum')
            ->getJson('/api/profile/seller');

        $getResponse->assertStatus(200)
            ->assertJsonPath('data.user_id', $seller->id);
    }
}
