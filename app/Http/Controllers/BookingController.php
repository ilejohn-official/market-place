<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Services\BookingManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingManagementService $bookingService;

    public function __construct(BookingManagementService $bookingService)
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
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get booking details
     */
    public function show(int $id): JsonResponse
    {
        $booking = $this->bookingService->getBookingDetails($id);

        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        return response()->json([
            'data' => $booking->load(['buyer', 'seller', 'service', 'escrowAccount']),
        ], 200);
    }

    /**
     * Update booking status
     */
    public function updateStatus(BookingRequest $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->updateBookingStatus(
                $request->user(),
                $id,
                $request->validated()['status']
            );

            return response()->json([
                'message' => 'Booking status updated successfully',
                'data' => $booking->load(['buyer', 'seller', 'service', 'escrowAccount']),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Booking not found' ? 404 : 403);
        }
    }

    /**
     * Get current user's bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);

            // Check if buyer or seller and get appropriate bookings
            $user = $request->user();
            if ($user->isBuyer()) {
                $result = $this->bookingService->getBuyerBookings($user, $page, $limit);
            } else if ($user->isSeller()) {
                $result = $this->bookingService->getSellerBookings($user, $page, $limit);
            } else {
                return response()->json([
                    'message' => 'Invalid user role',
                ], 403);
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
