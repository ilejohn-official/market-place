<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisputeRequest;
use App\Models\Booking;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;

class DisputeController extends Controller
{
    private DisputeService $disputeService;

    public function __construct(DisputeService $disputeService)
    {
        $this->disputeService = $disputeService;
    }

    /**
     * Create a dispute for a booking (buyer only)
     */
    public function store(DisputeRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $dispute = $this->disputeService->createDispute(
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
            'message' => 'Dispute created successfully',
            'data' => $dispute,
        ], 201);
    }
}
