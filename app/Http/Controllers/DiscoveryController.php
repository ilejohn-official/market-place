<?php

namespace App\Http\Controllers;

use App\Services\SellerDiscoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    private SellerDiscoveryService $sellerDiscoveryService;

    public function __construct(SellerDiscoveryService $sellerDiscoveryService)
    {
        $this->sellerDiscoveryService = $sellerDiscoveryService;
    }

    /**
     * List sellers (public)
     */
    public function sellers(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);
        $filters = $request->only(['search', 'sort_by']);

        $result = $this->sellerDiscoveryService->listSellers($page, $limit, $filters);

        return response()->json($result, 200);
    }
}
