<?php

namespace App\Services;

use App\Models\SellerProfile;

class SellerDiscoveryService
{
    public function listSellers(int $page = 1, int $limit = 15, array $filters = []): array
    {
        $query = SellerProfile::with('user')->withCount('services');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['sort_by']) && in_array($filters['sort_by'], ['rating', 'experience_level'], true)) {
            $query->orderBy($filters['sort_by'], 'desc');
        } else {
            $query->orderBy('rating', 'desc')
                ->orderByRaw(
                    "CASE experience_level WHEN 'expert' THEN 3 WHEN 'intermediate' THEN 2 WHEN 'beginner' THEN 1 ELSE 0 END DESC"
                );
        }

        $total = $query->count();
        $sellers = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'sellers' => $sellers->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}
