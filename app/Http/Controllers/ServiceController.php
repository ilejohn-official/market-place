<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use App\Services\ServiceManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    protected ServiceManagementService $serviceService;

    public function __construct(ServiceManagementService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Create a new service (seller only)
     */
    public function store(ServiceRequest $request): JsonResponse
    {
        try {
            $service = $this->serviceService->createService(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Service created successfully',
                'data' => $service,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get service details (public endpoint)
     */
    public function show(int $id): JsonResponse
    {
        $service = $this->serviceService->getServiceById($id);

        if (!$service) {
            return response()->json([
                'message' => 'Service not found',
            ], 404);
        }

        return response()->json([
            'data' => $service->load('seller'),
        ], 200);
    }

    /**
     * Get all services with filters (public endpoint)
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category', 'min_price', 'max_price', 'search', 'seller_id']);
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);

        $result = $this->serviceService->getAllServices($page, $limit, $filters);

        return response()->json($result, 200);
    }

    /**
     * Update service (seller only - owner)
     */
    public function update(ServiceRequest $request, int $id): JsonResponse
    {
        try {
            $service = $this->serviceService->updateService(
                $request->user(),
                $id,
                $request->validated()
            );

            return response()->json([
                'message' => 'Service updated successfully',
                'data' => $service,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Service not found' ? 404 : 403);
        }
    }

    /**
     * Soft delete service (seller only - owner)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->serviceService->deleteService($request->user(), $id);

            return response()->json([
                'message' => 'Service deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Service not found' ? 404 : 403);
        }
    }

    /**
     * Get current user's services (seller only)
     */
    public function myServices(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);

            $result = $this->serviceService->getSellerServices(
                $request->user(),
                $page,
                $limit
            );

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
