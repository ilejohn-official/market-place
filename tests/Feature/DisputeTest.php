<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\EscrowAccount;
use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class DisputeTest extends TestCase
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
            'status' => 'pending_approval',
        ]);

        EscrowAccount::create([
            'booking_id' => $this->booking->id,
            'total_amount' => 500,
            'platform_fee' => 50,
            'freelancer_amount' => 450,
            'status' => 'held',
        ]);
    }

    public function test_buyer_can_create_dispute()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/disputes", [
                'reason' => 'Work not delivered',
                'description' => 'Seller missed deadline',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Dispute created successfully')
            ->assertJsonPath('data.status', 'open');
    }

    public function test_admin_can_resolve_dispute_with_refund()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Wallet::updateOrCreate(
            ['user_id' => $this->buyer->id],
            ['balance' => 0, 'currency' => 'NGN']
        );

        $disputeResponse = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/disputes", [
                'reason' => 'Work not delivered',
            ]);

        $disputeId = $disputeResponse->json('data.id');

        $response = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/disputes/{$disputeId}/resolve", [
                'resolution_decision' => 'refund_to_buyer',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.resolution_decision', 'refund_to_buyer');
    }
}
