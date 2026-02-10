<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Message;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class MessageTest extends TestCase
{
    public function test_send_list_and_mark_read_messages(): void
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

        $sendResponse = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/messages", [
                'message_content' => 'Hello',
            ]);

        $sendResponse->assertStatus(201);

        $secondMessage = Message::create([
            'booking_id' => $booking->id,
            'sender_id' => $seller->id,
            'message_content' => 'Hi',
        ]);

        $listResponse = $this->actingAs($buyer, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}/messages");

        $listResponse->assertStatus(200)
            ->assertJsonPath('total', 2);

        $readResponse = $this->actingAs($buyer, 'sanctum')
            ->patchJson("/api/messages/{$secondMessage->id}/read");

        $readResponse->assertStatus(200)
            ->assertJsonPath('data.is_read', true);
    }
}
