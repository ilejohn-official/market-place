<?php

namespace App\Services;

use App\Models\Service;
use App\Models\User;
use Exception;

class ServiceManagementService
{
    /**
     * Create a new service
     */
    public function createService(User $seller, array $data): Service
    {
        if (!$seller->isSeller()) {
            throw new Exception('Only sellers can create services');
        }

        $service = Service::create([
            'seller_id' => $seller->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'price' => $data['price'],
            'estimated_days' => $data['estimated_days'],
            'tags' => $data['tags'] ?? [],
            'is_active' => true,
        ]);

        return $service;
    }

    /**
     * Get service by ID
     */
    public function getServiceById(int $serviceId): ?Service
    {
        return Service::find($serviceId);
    }

    /**
     * Get all active services with pagination
     */
    public function getAllServices(int $page = 1, int $limit = 15, array $filters = []): array
    {
        $query = Service::where('is_active', true)->where('deleted_at', null);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['seller_id'])) {
            $query->where('seller_id', $filters['seller_id']);
        }

        $total = $query->count();
        $services = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'services' => $services->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * Update service
     */
    public function updateService(User $seller, int $serviceId, array $data): Service
    {
        $service = Service::find($serviceId);

        if (!$service) {
            throw new Exception('Service not found');
        }

        if ($service->seller_id !== $seller->id) {
            throw new Exception('You can only update your own services');
        }

        $service->update([
            'title' => $data['title'] ?? $service->title,
            'description' => $data['description'] ?? $service->description,
            'category' => $data['category'] ?? $service->category,
            'price' => $data['price'] ?? $service->price,
            'estimated_days' => $data['estimated_days'] ?? $service->estimated_days,
            'tags' => $data['tags'] ?? $service->tags,
            'is_active' => $data['is_active'] ?? $service->is_active,
        ]);

        return $service;
    }

    /**
     * Soft delete service
     */
    public function deleteService(User $seller, int $serviceId): bool
    {
        $service = Service::find($serviceId);

        if (!$service) {
            throw new Exception('Service not found');
        }

        if ($service->seller_id !== $seller->id) {
            throw new Exception('You can only delete your own services');
        }

        $service->delete();
        return true;
    }

    /**
     * Get seller's services
     */
    public function getSellerServices(User $seller, int $page = 1, int $limit = 15): array
    {
        if (!$seller->isSeller()) {
            throw new Exception('Only sellers have services');
        }

        $query = Service::where('seller_id', $seller->id);
        $total = $query->count();
        $services = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'services' => $services->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}
