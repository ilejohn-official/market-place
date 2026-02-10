<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $service = Service::factory()->create(['seller_id' => $seller->id]);

        return [
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'service_id' => $service->id,
            'agreed_amount' => $this->faker->numberBetween(100, 5000),
            'status' => $this->faker->randomElement([
                'pending_negotiation',
                'in_progress',
                'pending_approval',
                'completed',
            ]),
            'negotiation_notes' => $this->faker->paragraph(),
        ];
    }
}
