<?php

namespace App\Http\Controllers;

use App\Http\Requests\EscrowRequest;
use App\Models\Booking;
use App\Services\EscrowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EscrowController extends Controller
{
    private EscrowService $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Create escrow for a booking (buyer only)
     */
    public function store(EscrowRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $escrow = $this->escrowService->createEscrow(
                $request->user(),
                $booking,
                (float) $request->validated()['amount']
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Escrow created successfully',
            'data' => $escrow,
        ], 201);
    }

    /**
     * Get escrow details for a booking
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $escrow = $this->escrowService->getEscrow($request->user(), $booking);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'data' => $escrow,
        ], 200);
    }
}
