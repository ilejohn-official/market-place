<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\SellerProfile;
use App\Models\User;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    private User $seller;
    private User $buyer;
    private SellerProfile $sellerProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a seller user
        $this->seller = User::factory()->create(['role' => 'seller']);
        
        // Create seller profile
        $this->sellerProfile = SellerProfile::create([
            'user_id' => $this->seller->id,
            'hourly_rate' => 50,
            'experience_level' => 'intermediate',
            'bio' => 'Experienced seller',
        ]);

        // Create a buyer user
        $this->buyer = User::factory()->create(['role' => 'buyer']);
    }

    // ========== CREATE SERVICE TESTS ==========

    public function test_seller_can_create_service()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => 500,
            'estimated_days' => 7,
            'tags' => ['php', 'laravel'],
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/services', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'seller_id',
                    'title',
                    'description',
                    'category',
                    'price',
                    'estimated_days',
                    'tags',
                    'is_active',
                ],
            ])
            ->assertJsonPath('message', 'Service created successfully')
            ->assertJsonPath('data.title', 'Web Development')
            ->assertJsonPath('data.seller_id', $this->seller->id);

        $this->assertDatabaseHas('services', [
            'seller_id' => $this->seller->id,
            'title' => 'Web Development',
            'price' => 500,
        ]);
    }

    public function test_buyer_cannot_create_service()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => 500,
            'estimated_days' => 7,
            'tags' => ['php', 'laravel'],
        ];

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/api/services', $data);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Only sellers can create services');
    }

    public function test_unauthenticated_user_cannot_create_service()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => 500,
            'estimated_days' => 7,
            'tags' => ['php', 'laravel'],
        ];

        $response = $this->postJson('/api/services', $data);

        $response->assertStatus(401);
    }

    public function test_create_service_validates_required_fields()
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/services', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'category', 'price', 'estimated_days']);
    }

    public function test_create_service_validates_price_greater_than_zero()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => 0,
            'estimated_days' => 7,
            'tags' => ['php'],
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/services', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_service_validates_negative_price()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => -100,
            'estimated_days' => 7,
            'tags' => ['php'],
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/services', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    public function test_create_service_validates_estimated_days_positive()
    {
        $data = [
            'title' => 'Web Development',
            'description' => 'I will build a website for you',
            'category' => 'web-development',
            'price' => 500,
            'estimated_days' => 0,
            'tags' => ['php'],
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->postJson('/api/services', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['estimated_days']);
    }

    // ========== READ SERVICE TESTS ==========

    public function test_anyone_can_view_service_details()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'seller_id',
                    'title',
                    'description',
                    'category',
                    'price',
                    'estimated_days',
                    'seller',
                ],
            ])
            ->assertJsonPath('data.id', $service->id)
            ->assertJsonPath('data.seller_id', $this->seller->id);
    }

    public function test_view_nonexistent_service_returns_404()
    {
        $response = $this->getJson('/api/services/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Service not found');
    }

    public function test_get_all_services_returns_paginated_list()
    {
        Service::factory(5)->create(['seller_id' => $this->seller->id]);

        $response = $this->getJson('/api/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'services',
                'total',
                'page',
                'limit',
                'pages',
            ])
            ->assertJsonPath('total', 5)
            ->assertJsonPath('page', 1);

        $this->assertCount(5, $response->json('services'));
    }

    public function test_get_services_with_pagination()
    {
        Service::factory(30)->create(['seller_id' => $this->seller->id]);

        $response = $this->getJson('/api/services?page=2&limit=10');

        $response->assertStatus(200)
            ->assertJsonPath('page', 2)
            ->assertJsonPath('limit', 10)
            ->assertJsonPath('pages', 3);

        $this->assertCount(10, $response->json('services'));
    }

    public function test_filter_services_by_category()
    {
        Service::factory(3)->create(['seller_id' => $this->seller->id, 'category' => 'web-development']);
        Service::factory(2)->create(['seller_id' => $this->seller->id, 'category' => 'design']);

        $response = $this->getJson('/api/services?category=web-development');

        $response->assertStatus(200)
            ->assertJsonPath('total', 3);

        $services = $response->json('services');
        foreach ($services as $service) {
            $this->assertEquals('web-development', $service['category']);
        }
    }

    public function test_filter_services_by_price_range()
    {
        Service::factory()->create(['seller_id' => $this->seller->id, 'price' => 100]);
        Service::factory()->create(['seller_id' => $this->seller->id, 'price' => 500]);
        Service::factory()->create(['seller_id' => $this->seller->id, 'price' => 1000]);

        $response = $this->getJson('/api/services?min_price=200&max_price=800');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);

        $services = $response->json('services');
        $this->assertEquals(500, $services[0]['price']);
    }

    public function test_search_services_by_title()
    {
        Service::factory()->create(['seller_id' => $this->seller->id, 'title' => 'PHP Development']);
        Service::factory()->create(['seller_id' => $this->seller->id, 'title' => 'Web Design']);

        $response = $this->getJson('/api/services?search=PHP');

        $response->assertStatus(200)
            ->assertJsonPath('total', 1);

        $services = $response->json('services');
        $this->assertStringContainsString('PHP', $services[0]['title']);
    }

    public function test_filter_services_by_seller()
    {
        $anotherSeller = User::factory()->create(['role' => 'seller']);
        Service::factory(2)->create(['seller_id' => $this->seller->id]);
        Service::factory(3)->create(['seller_id' => $anotherSeller->id]);

        $response = $this->getJson("/api/services?seller_id={$this->seller->id}");

        $response->assertStatus(200)
            ->assertJsonPath('total', 2);
    }

    // ========== UPDATE SERVICE TESTS ==========

    public function test_seller_owner_can_update_service()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $data = [
            'title' => 'Updated Title',
            'price' => 750,
        ];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson("/api/services/{$service->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Service updated successfully')
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.price', 750);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'title' => 'Updated Title',
            'price' => 750,
        ]);
    }

    public function test_seller_owner_can_partially_update_service()
    {
        $service = Service::factory()->create([
            'seller_id' => $this->seller->id,
            'title' => 'Original Title',
            'price' => 500,
        ]);

        $data = ['title' => 'New Title'];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson("/api/services/{$service->id}", $data);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'New Title')
            ->assertJsonPath('data.price', 500);
    }

    public function test_seller_non_owner_cannot_update_service()
    {
        $anotherSeller = User::factory()->create(['role' => 'seller']);
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $data = ['title' => 'Hacked Title'];

        $response = $this->actingAs($anotherSeller, 'sanctum')
            ->putJson("/api/services/{$service->id}", $data);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You can only update your own services');
    }

    public function test_buyer_cannot_update_service()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $data = ['title' => 'Hacked Title'];

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->putJson("/api/services/{$service->id}", $data);

        $response->assertStatus(403);
    }

    public function test_update_nonexistent_service_returns_404()
    {
        $data = ['title' => 'New Title'];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson('/api/services/9999', $data);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Service not found');
    }

    public function test_update_service_validates_price()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $data = ['price' => 0];

        $response = $this->actingAs($this->seller, 'sanctum')
            ->putJson("/api/services/{$service->id}", $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);
    }

    // ========== DELETE SERVICE TESTS ==========

    public function test_seller_owner_can_delete_service()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);
        $serviceId = $service->id;

        $response = $this->actingAs($this->seller, 'sanctum')
            ->deleteJson("/api/services/{$serviceId}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Service deleted successfully');

        // Check soft delete - record still exists but with deleted_at
        $deletedService = Service::withTrashed()->find($serviceId);
        $this->assertNotNull($deletedService->deleted_at);
    }

    public function test_seller_non_owner_cannot_delete_service()
    {
        $anotherSeller = User::factory()->create(['role' => 'seller']);
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $response = $this->actingAs($anotherSeller, 'sanctum')
            ->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You can only delete your own services');

        // Service should still exist (not deleted)
        $this->assertDatabaseHas('services', ['id' => $service->id]);
    }

    public function test_buyer_cannot_delete_service()
    {
        $service = Service::factory()->create(['seller_id' => $this->seller->id]);

        $response = $this->actingAs($this->buyer, 'sanctum')
            ->deleteJson("/api/services/{$service->id}");

        $response->assertStatus(403);
    }

    public function test_delete_nonexistent_service_returns_404()
    {
        $response = $this->actingAs($this->seller, 'sanctum')
            ->deleteJson('/api/services/9999');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Service not found');
    }

    // ========== MY SERVICES ENDPOINT TESTS ==========

    public function test_seller_can_view_own_services()
    {
        Service::factory(3)->create(['seller_id' => $this->seller->id]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson('/api/my-services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'services',
                'total',
                'page',
                'limit',
                'pages',
            ])
            ->assertJsonPath('total', 3);

        $this->assertCount(3, $response->json('services'));
    }

    public function test_buyer_cannot_view_my_services()
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->getJson('/api/my-services');

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Only sellers have services');
    }

    public function test_my_services_excludes_other_sellers_services()
    {
        $anotherSeller = User::factory()->create(['role' => 'seller']);
        Service::factory(2)->create(['seller_id' => $this->seller->id]);
        Service::factory(3)->create(['seller_id' => $anotherSeller->id]);

        $response = $this->actingAs($this->seller, 'sanctum')
            ->getJson('/api/my-services');

        $response->assertStatus(200)
            ->assertJsonPath('total', 2);

        $services = $response->json('services');
        foreach ($services as $service) {
            $this->assertEquals($this->seller->id, $service['seller_id']);
        }
    }

    public function test_unauthenticated_user_cannot_access_my_services()
    {
        $response = $this->getJson('/api/my-services');

        $response->assertStatus(401);
    }
}
