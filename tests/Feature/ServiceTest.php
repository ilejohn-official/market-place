<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    public function test_seller_can_create_and_manage_services(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);

        $createResponse = $this->actingAs($seller, 'sanctum')
            ->postJson('/api/services', [
                'title' => 'Web Development',
                'description' => 'Build a website',
                'category' => 'web-dev',
                'price' => 500,
                'estimated_days' => 7,
            ]);

        $createResponse->assertStatus(201)
            ->assertJsonPath('data.title', 'Web Development');

        $serviceId = $createResponse->json('data.id');

        $showResponse = $this->getJson("/api/services/{$serviceId}");

        $showResponse->assertStatus(200)
            ->assertJsonPath('data.id', $serviceId);

        $listResponse = $this->getJson('/api/services');

        $listResponse->assertStatus(200)
            ->assertJsonPath('total', 1);

        $updateResponse = $this->actingAs($seller, 'sanctum')
            ->putJson("/api/services/{$serviceId}", [
                'title' => 'Updated Service',
            ]);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Service');

        $deleteResponse = $this->actingAs($seller, 'sanctum')
            ->deleteJson("/api/services/{$serviceId}");

        $deleteResponse->assertStatus(200);

        $service = Service::withTrashed()->find($serviceId);
        $this->assertNotNull($service->deleted_at);
    }
}
