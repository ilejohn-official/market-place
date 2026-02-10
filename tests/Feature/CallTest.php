<?php

namespace Tests\Feature;

use App\Events\CallInitiated;
use App\Models\Booking;
use App\Models\Call;
use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CallTest extends TestCase
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
    }

    public function test_participant_can_initiate_call()
    {
        Event::fake([CallInitiated::class]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/calls", [
                'call_type' => 'audio',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Call initiated successfully')
            ->assertJsonPath('data.call.call_type', 'audio');

        $this->assertNotNull($response->json('data.initiator_token'));
        Event::assertDispatched(CallInitiated::class);
    }

    public function test_non_participant_cannot_initiate_call()
    {
        $otherBuyer = User::factory()->create(['role' => 'buyer']);

        $response = $this->actingAs($otherBuyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/calls", [
                'call_type' => 'video',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not authorized to call for this booking');
    }

    public function test_participant_can_update_call_status()
    {
        $call = Call::create([
            'booking_id' => $this->booking->id,
            'initiator_id' => $this->buyer->id,
            'receiver_id' => $this->seller->id,
            'call_type' => 'audio',
            'status' => 'initiated',
            'started_at' => now()->subSeconds(30),
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->patchJson("/api/calls/{$call->id}", [
                'status' => 'completed',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Call updated successfully')
            ->assertJsonPath('data.status', 'completed');
    }
}
