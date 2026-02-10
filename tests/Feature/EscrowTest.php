<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class EscrowTest extends TestCase
{
    private User $buyer;
    private User $seller;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->seller = User::factory()->create(['role' => 'seller']);

        SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50,
            'experience_level' => 'intermediate',
        ]);

        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $this->booking = Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $service->id,
            'agreed_amount' => 500,
            'status' => 'pending_negotiation',
        ]);

        Wallet::updateOrCreate(
            ['user_id' => $this->buyer->id],
            ['balance' => 1000, 'currency' => 'NGN']
        );
    }

    public function test_buyer_can_create_escrow()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/escrow", [
                'amount' => 500,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Escrow created successfully')
            ->assertJsonPath('data.total_amount', '500.00');

        $this->assertDatabaseHas('escrow_accounts', [
            'booking_id' => $this->booking->id,
            'total_amount' => 500,
            'status' => 'held',
        ]);
    }

    public function test_create_escrow_with_insufficient_balance_fails()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/escrow", [
                'amount' => 2000,
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Insufficient wallet balance');
    }
}
