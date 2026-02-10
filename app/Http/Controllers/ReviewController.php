<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Booking;
use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Post review for a booking (buyer only)
     */
    public function store(ReviewRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $review = $this->reviewService->createReview(
                $request->user(),
                $booking,
                $request->validated()
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review,
        ], 201);
    }

    /**
     * Get seller reviews
     */
    public function index(Request $request, int $sellerId): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);

        $result = $this->reviewService->getSellerReviews($sellerId, $page, $limit);

        return response()->json($result, 200);
    }
}
