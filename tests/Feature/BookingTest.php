<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_booking_flow_with_escrow_completion_approval_and_cancel(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = User::factory()->create(['role' => 'seller']);

        SellerProfile::create([
            'user_id' => $seller->id,
            'hourly_rate' => 50,
            'experience_level' => 'intermediate',
        ]);

        $service = Service::factory()->create(['seller_id' => $seller->id]);

        Wallet::updateOrCreate(
            ['user_id' => $buyer->id],
            ['balance' => 1000, 'currency' => 'NGN']
        );

        $createResponse = $this->actingAs($buyer, 'sanctum')
            ->postJson('/api/bookings', [
                'service_id' => $service->id,
                'seller_id' => $seller->id,
                'proposed_amount' => 500,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'pending_negotiation');

        $bookingId = $createResponse->json('data.id');

        $escrowResponse = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/bookings/{$bookingId}/escrow", [
                'amount' => 500,
            ]);

        $escrowResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'held');

        $completeResponse = $this->actingAs($seller, 'sanctum')
            ->patchJson("/api/bookings/{$bookingId}/mark-complete");

        $completeResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'pending_approval');

        $approveResponse = $this->actingAs($buyer, 'sanctum')
            ->patchJson("/api/bookings/{$bookingId}/approve");

        $approveResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');

        $cancelBooking = Booking::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'service_id' => $service->id,
            'agreed_amount' => 500,
            'status' => 'pending_negotiation',
        ]);

        $cancelResponse = $this->actingAs($buyer, 'sanctum')
            ->patchJson("/api/bookings/{$cancelBooking->id}/cancel", [
                'reason' => 'No longer needed',
            ]);

        $cancelResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');
    }
}
