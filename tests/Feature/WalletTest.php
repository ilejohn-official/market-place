<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WalletTest extends TestCase
{
    public function test_user_can_view_wallet_balance(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'user_id', 'balance', 'currency']]);
    }
}
