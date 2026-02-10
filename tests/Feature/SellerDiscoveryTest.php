<?php

namespace Tests\Feature;

use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class SellerDiscoveryTest extends TestCase
{
    public function test_public_can_list_sellers(): void
    {
        $sellers = User::factory()
            ->count(2)
            ->create(['role' => 'seller']);

        foreach ($sellers as $seller) {
            SellerProfile::create([
                'user_id' => $seller->id,
                'hourly_rate' => 50,
                'experience_level' => 'intermediate',
            ]);

            Service::factory()->create(['seller_id' => $seller->id]);
        }

        $response = $this->getJson('/api/sellers');

        $response->assertStatus(200)
            ->assertJsonPath('total', 2);
    }
}
