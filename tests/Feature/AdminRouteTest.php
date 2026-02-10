<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\EscrowAccount;
use App\Models\SellerProfile;
use App\Models\Service;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class AdminRouteTest extends TestCase
{
    public function test_admin_can_list_and_resolve_disputes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
            'status' => 'disputed',
        ]);

        EscrowAccount::create([
            'booking_id' => $booking->id,
            'total_amount' => 500,
            'platform_fee' => 50,
            'freelancer_amount' => 450,
            'status' => 'held',
        ]);

        Dispute::create([
            'booking_id' => $booking->id,
            'created_by_id' => $buyer->id,
            'reason' => 'Issue',
            'status' => 'open',
        ]);

        Wallet::updateOrCreate(
            ['user_id' => $seller->id],
            ['balance' => 0, 'currency' => 'NGN']
        );

        $listResponse = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/disputes');

        $listResponse->assertStatus(200)
            ->assertJsonPath('total', 1);

        $resolveResponse = $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/disputes/{$booking->dispute->id}/resolve", [
                'resolution_decision' => 'release_to_seller',
            ]);

        $resolveResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'resolved')
            ->assertJsonPath('data.resolution_decision', 'release_to_seller');

        $booking->refresh();
        $escrow = $booking->escrowAccount()->first();
        $sellerWallet = Wallet::where('user_id', $seller->id)->first();

        $this->assertEquals('completed', $booking->status);
        $this->assertEquals('released', $escrow->status);
        $this->assertEquals(450, (float) $sellerWallet->balance);
    }
}
