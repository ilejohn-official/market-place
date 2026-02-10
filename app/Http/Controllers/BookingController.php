<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Create a new booking (buyer only)
     */
    public function store(BookingRequest $request): JsonResponse
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Booking created successfully',
                'data' => $booking->load(['buyer', 'seller', 'service', 'escrowAccount']),
            ], 201);
        } catch (\Exception $e) {
            if (in_array($e->getMessage(), ['Seller not found', 'Service not found'], true)) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 404);
            }

            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get booking details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingForUser($request->user(), $id);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Booking not found' ? 404 : 403);
        }

        return response()->json([
            'data' => $booking->load(['buyer', 'seller', 'service', 'escrowAccount']),
        ], 200);
    }

    /**
     * Get current user's bookings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);
            $filters = $request->only(['status']);

            $result = $this->bookingService->getUserBookings($request->user(), $page, $limit, $filters);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
