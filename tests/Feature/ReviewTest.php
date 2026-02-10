<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    public function test_buyer_can_review_completed_booking_and_list_reviews(): void
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
            'status' => 'completed',
        ]);

        $createResponse = $this->actingAs($buyer, 'sanctum')
            ->postJson("/api/bookings/{$booking->id}/review", [
                'rating' => 5,
                'review_text' => 'Great work',
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.rating', 5);

        $listResponse = $this->getJson("/api/sellers/{$seller->id}/reviews");

        $listResponse->assertStatus(200)
            ->assertJsonPath('total', 1);

        $profile = SellerProfile::where('user_id', $seller->id)->first();
        $this->assertEquals(5, (float) $profile->rating);
    }
}
