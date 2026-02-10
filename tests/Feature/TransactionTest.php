<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    public function test_user_can_view_transactions(): void
    {
        $user = User::factory()->create();

        Transaction::create([
            'booking_id' => null,
            'user_id' => $user->id,
            'transaction_type' => 'payout',
            'amount' => 100,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);
    }
}
