<?php

namespace App\Http\Controllers;

use App\Http\Requests\SellerProfileRequest;
use App\Services\SellerProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SellerProfileController extends Controller
{
    private SellerProfileService $sellerProfileService;

    public function __construct(SellerProfileService $sellerProfileService)
    {
        $this->sellerProfileService = $sellerProfileService;
    }

    /**
     * Create seller profile
     *
     * @param SellerProfileRequest $request
     * @return JsonResponse
     */
    public function store(SellerProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->sellerProfileService->createOrUpdateProfile(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Seller profile created successfully',
                'data' => $profile,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create seller profile',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get own seller profile
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        try {
            $profile = $this->sellerProfileService->getProfileByUser($request->user());

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $profile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve seller profile',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Update seller profile
     *
     * @param SellerProfileRequest $request
     * @return JsonResponse
     */
    public function update(SellerProfileRequest $request): JsonResponse
    {
        try {
            $profile = $this->sellerProfileService->updateProfile(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Seller profile updated successfully',
                'data' => $profile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update seller profile',
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get seller profile by ID (public endpoint)
     *
     * @param int $sellerId
     * @return JsonResponse
     */
    public function showPublic(int $sellerId): JsonResponse
    {
        try {
            $profile = $this->sellerProfileService->getProfileById($sellerId);

            if (!$profile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seller profile not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $profile,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve seller profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
