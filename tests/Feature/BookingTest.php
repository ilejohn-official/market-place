<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\EscrowAccount;
use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use Tests\TestCase;

class BookingTest extends TestCase
{
    private User $buyer;
    private User $seller;
    private Service $service;

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

        $this->service = Service::factory()->create(['seller_id' => $this->seller->id]);
    }

    public function test_buyer_can_create_booking()
    {
        $data = [
            'service_id' => $this->service->id,
            'seller_id' => $this->seller->id,
            'proposed_amount' => 500,
            'description' => 'Need web development work',
        ];

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/bookings', $data);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Booking created successfully')
            ->assertJsonPath('data.status', 'pending_negotiation')
            ->assertJsonPath('data.agreed_amount', 500);

        $this->assertDatabaseHas('bookings', [
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 500,
        ]);
    }

    public function test_seller_cannot_create_booking()
    {
        $data = [
            'service_id' => $this->service->id,
            'seller_id' => $this->seller->id,
            'proposed_amount' => 500,
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/bookings', $data);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Only buyers can create bookings');
    }

    public function test_create_booking_creates_escrow_account()
    {
        $data = [
            'service_id' => $this->service->id,
            'seller_id' => $this->seller->id,
            'proposed_amount' => 1000,
        ];

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/bookings', $data);

        $response->assertStatus(201);
        
        $bookingId = $response->json('data.id');
        $this->assertDatabaseHas('escrow_accounts', [
            'booking_id' => $bookingId,
            'total_amount' => 1000,
            'platform_fee' => 100, // 10%
            'freelancer_amount' => 900, // 90%
            'status' => 'held',
        ]);
    }

    public function test_booking_validates_required_fields()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/bookings', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_id', 'seller_id', 'proposed_amount']);
    }

    public function test_booking_validates_positive_amount()
    {
        $data = [
            'service_id' => $this->service->id,
            'seller_id' => $this->seller->id,
            'proposed_amount' => 0,
        ];

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/bookings', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['proposed_amount']);
    }

    public function test_anyone_can_view_booking_details()
    {
        $booking = Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 500,
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson("/api/bookings/{$booking->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $booking->id);

        $this->assertEquals(
            $booking->agreed_amount,
            data_get($response->json(), 'data.agreed_amount')
        );
    }

    public function test_view_nonexistent_booking_returns_404()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/bookings/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Booking not found');
    }

    public function test_buyer_or_seller_can_update_booking_status()
    {
        $booking = Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 500,
            'status' => 'pending_negotiation',
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->putJson("/api/bookings/{$booking->id}/status", [
                'status' => 'in_progress',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'in_progress');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_unauthorized_user_cannot_update_booking_status()
    {
        $otherBuyer = User::factory()->create(['role' => 'buyer']);
        $booking = Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 500,
        ]);

        $response = $this->actingAs($otherBuyer, 'sanctum')
            ->putJson("/api/bookings/{$booking->id}/status", [
                'status' => 'completed',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not authorized to update this booking');
    }

    public function test_buyer_can_view_own_bookings()
    {
        Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 300,
        ]);
        Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 400,
        ]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/my-bookings');

        $response->assertStatus(200)
            ->assertJsonPath('total', 2);
    }

    public function test_seller_can_view_own_bookings()
    {
        Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 300,
        ]);
        Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 400,
        ]);
        Booking::create([
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'service_id' => $this->service->id,
            'agreed_amount' => 500,
        ]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson('/api/my-bookings');

        $response->assertStatus(200)
            ->assertJsonPath('total', 3);
    }
}
