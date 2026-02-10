<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Call;
use App\Models\Dispute;
use App\Models\Message;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Tests\TestCase;

class CommunicationAndTransactionTest extends TestCase
{
    private User $seller;
    private User $buyer;
    private Service $service;
    private Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create(['role' => 'seller']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);
        $this->service = Service::create([
            'seller_id' => $this->seller->id,
            'title' => 'Web Development',
            'description' => 'Full stack web development',
            'category' => 'programming',
            'price' => 100.00,
        ]);
        $this->booking = Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 100.00,
        ]);
    }

    /**
     * Test send message to booking
     */
    public function test_send_message_in_booking(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/messages", [
                'message_content' => 'Hello, when can you start?',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('messages', [
            'booking_id' => $this->booking->id,
            'sender_id' => $this->buyer->id,
            'message_content' => 'Hello, when can you start?',
        ]);
    }

    /**
     * Test get messages for booking
     */
    public function test_get_messages_for_booking(): void
    {
        Message::create([
            'booking_id' => $this->booking->id,
            'sender_id' => $this->buyer->id,
            'message_content' => 'Test message',
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson("/api/bookings/{$this->booking->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test non-participant cannot view messages
     */
    public function test_non_participant_cannot_view_messages(): void
    {
        $other = User::factory()->create();

        $response = $this->actingAs($other, 'sanctum')
            ->getJson("/api/bookings/{$this->booking->id}/messages");

        $response->assertStatus(403);
    }

    /**
     * Test mark message as read
     */
    public function test_mark_message_as_read(): void
    {
        $message = Message::create([
            'booking_id' => $this->booking->id,
            'sender_id' => $this->buyer->id,
            'message_content' => 'Test message',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->patchJson("/api/messages/{$message->id}/read");

        $response->assertStatus(200);
        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'is_read' => true,
        ]);
    }

    /**
     * Test initiate audio call
     */
    public function test_initiate_audio_call(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/calls", [
                'call_type' => 'audio',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['call_id', 'initiator_token']]);

        $this->assertDatabaseHas('calls', [
            'booking_id' => $this->booking->id,
            'initiator_id' => $this->buyer->id,
            'call_type' => 'audio',
            'status' => 'initiated',
        ]);
    }

    /**
     * Test initiate video call
     */
    public function test_initiate_video_call(): void
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/calls", [
                'call_type' => 'video',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('calls', [
            'booking_id' => $this->booking->id,
            'call_type' => 'video',
        ]);
    }

    /**
     * Test non-participant cannot initiate call
     */
    public function test_non_participant_cannot_initiate_call(): void
    {
        $other = User::factory()->create();

        $response = $this->actingAs($other, 'sanctum')
            ->postJson("/api/bookings/{$this->booking->id}/calls", [
                'call_type' => 'audio',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test update call status
     */
    public function test_update_call_status(): void
    {
        $call = Call::create([
            'booking_id' => $this->booking->id,
            'initiator_id' => $this->buyer->id,
            'receiver_id' => $this->seller->id,
            'call_type' => 'audio',
            'status' => 'initiated',
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->patchJson("/api/calls/{$call->id}", [
                'status' => 'accepted',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('calls', [
            'id' => $call->id,
            'status' => 'accepted',
        ]);
    }

    /**
     * Test get wallet balance
     */
    public function test_get_wallet_balance(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/wallet');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['balance', 'currency']]);
    }

    /**
     * Test get transaction history
     */
    public function test_get_transaction_history(): void
    {
        Transaction::create([
            'user_id' => $this->buyer->id,
            'booking_id' => $this->booking->id,
            'transaction_type' => 'escrow_hold',
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/transactions');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /**
     * Test get specific transaction
     */
    public function test_get_specific_transaction(): void
    {
        $transaction = Transaction::create([
            'user_id' => $this->buyer->id,
            'booking_id' => $this->booking->id,
            'transaction_type' => 'escrow_hold',
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson("/api/transactions/{$transaction->id}");

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $transaction->id]]);
    }

    /**
     * Test create dispute
     */
    public function test_create_dispute(): void
    {
        $this->booking->update(['status' => 'in_progress']);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/disputes', [
                'booking_id' => $this->booking->id,
                'reason' => 'Work not completed',
                'description' => 'The freelancer did not finish the project',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('disputes', [
            'booking_id' => $this->booking->id,
            'created_by_id' => $this->buyer->id,
            'reason' => 'Work not completed',
            'status' => 'open',
        ]);
    }

    /**
     * Test get dispute details
     */
    public function test_get_dispute_details(): void
    {
        $dispute = Dispute::create([
            'booking_id' => $this->booking->id,
            'created_by_id' => $this->buyer->id,
            'reason' => 'Work not completed',
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson("/api/disputes/{$dispute->id}");

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $dispute->id]]);
    }

    /**
     * Test only buyer can create dispute
     */
    public function test_only_buyer_can_create_dispute(): void
    {
        $this->booking->update(['status' => 'in_progress']);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/disputes', [
                'booking_id' => $this->booking->id,
                'reason' => 'Work not completed',
                'description' => 'The buyer claims work is not done',
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test cannot create dispute for completed booking
     */
    public function test_cannot_dispute_completed_booking(): void
    {
        $this->booking->update(['status' => 'completed']);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/disputes', [
                'booking_id' => $this->booking->id,
                'reason' => 'Work not completed',
                'description' => 'The freelancer did not finish',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test unauthorized access to endpoints
     */
    public function test_unauthorized_access_to_messages(): void
    {
        $response = $this->getJson("/api/bookings/{$this->booking->id}/messages");

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to calls
     */
    public function test_unauthorized_access_to_calls(): void
    {
        $response = $this->postJson("/api/bookings/{$this->booking->id}/calls", [
            'call_type' => 'audio',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to wallet
     */
    public function test_unauthorized_access_to_wallet(): void
    {
        $response = $this->getJson('/api/wallet');

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to transactions
     */
    public function test_unauthorized_access_to_transactions(): void
    {
        $response = $this->getJson('/api/transactions');

        $response->assertStatus(401);
    }

    /**
     * Test unauthorized access to disputes
     */
    public function test_unauthorized_access_to_disputes(): void
    {
        $response = $this->postJson('/api/disputes', [
            'booking_id' => $this->booking->id,
            'reason' => 'Work not completed',
        ]);

        $response->assertStatus(401);
    }
}
