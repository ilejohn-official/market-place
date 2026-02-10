<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Services\BookingCancellationService;
use App\Services\BookingService;
use App\Services\PayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;
    protected PayoutService $payoutService;
    protected BookingCancellationService $cancellationService;

    public function __construct(
        BookingService $bookingService,
        PayoutService $payoutService,
        BookingCancellationService $cancellationService
    )
    {
        $this->bookingService = $bookingService;
        $this->payoutService = $payoutService;
        $this->cancellationService = $cancellationService;
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

    /**
     * Mark work as complete (seller only)
     */
    public function markComplete(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingForUser($request->user(), $id);
            $booking = $this->bookingService->markComplete($request->user(), $booking);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Booking not found' ? 404 : 403);
        }

        return response()->json([
            'message' => 'Booking marked as complete',
            'data' => $booking,
        ], 200);
    }

    /**
     * Approve completion and release funds (buyer only)
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingForUser($request->user(), $id);
            $booking = $this->payoutService->releaseFunds($request->user(), $booking);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Booking not found' ? 404 : 403);
        }

        return response()->json([
            'message' => 'Booking approved successfully',
            'data' => $booking,
        ], 200);
    }

    /**
     * Cancel booking (buyer or seller)
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        try {
            $booking = $this->bookingService->getBookingForUser($request->user(), $id);
            $booking = $this->cancellationService->cancel(
                $request->user(),
                $booking,
                $request->input('reason')
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getMessage() === 'Booking not found' ? 404 : 403);
        }

        return response()->json([
            'message' => 'Booking cancelled successfully',
            'data' => $booking,
        ], 200);
    }
}
