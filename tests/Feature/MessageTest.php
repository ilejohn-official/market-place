<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Message;
use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use Tests\TestCase;

class MessageTest extends TestCase
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

    public function test_participant_can_send_message()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/messages", [
                'message_content' => 'Hello seller',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Message sent successfully')
            ->assertJsonPath('data.message_content', 'Hello seller');
    }

    public function test_non_participant_cannot_send_message()
    {
        $otherBuyer = User::factory()->create(['role' => 'buyer']);

        $response = $this->actingAs($otherBuyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/messages", [
                'message_content' => 'Hello',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not authorized to send messages for this booking');
    }

    public function test_participant_can_list_messages()
    {
        Message::create([
            'booking_id' => $this->booking->id,
            'sender_id' => $this->buyer->id,
            'message_content' => 'First message',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson("/api/bookings/{$this->booking->id}/messages");

        $response->assertStatus(200)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('messages.0.message_content', 'First message');
    }

    public function test_participant_can_mark_message_as_read()
    {
        $message = Message::create([
            'booking_id' => $this->booking->id,
            'sender_id' => $this->buyer->id,
            'message_content' => 'Read me',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->patchJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Message marked as read')
            ->assertJsonPath('data.is_read', true);
    }
}
