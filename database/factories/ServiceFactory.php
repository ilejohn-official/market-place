<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->create(['role' => 'seller'])->id,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement([
                'web-development',
                'design',
                'writing',
                'marketing',
                'programming',
            ]),
            'price' => $this->faker->numberBetween(100, 5000),
            'estimated_days' => $this->faker->numberBetween(1, 30),
            'tags' => $this->faker->words(3),
            'is_active' => true,
        ];
    }
}
