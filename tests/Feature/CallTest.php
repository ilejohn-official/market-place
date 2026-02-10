<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class CallTest extends TestCase
{
    public function test_initiate_and_update_call(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = User::factory()->create(['role' => 'seller']);

        SellerProfile::create([
            'user_id' => $seller->id,
            'hourly_rate' => 50,
            'experience_level' => 'intermediate',
        ]);

        $service = Service::factory()->create(['seller_id' => $seller->id]);
        $booking = Booking::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'service_id' => $service->id,
            'agreed_amount' => 500,
            'status' => 'pending_negotiation',
        ]);

        $initResponse = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/calls", [
                'call_type' => 'audio',
            ]);

        $initResponse->assertStatus(201)
            ->assertJsonPath('data.call.call_type', 'audio');

        $callId = $initResponse->json('data.call.id');

        $updateResponse = $this->actingAs($seller, 'sanctum')
            ->patchJson("/api/calls/{$callId}", [
                'status' => 'completed',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'completed');
    }
}
