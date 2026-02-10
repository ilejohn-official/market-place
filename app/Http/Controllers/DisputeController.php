<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisputeRequest;
use App\Http\Requests\DisputeResolutionRequest;
use App\Models\Booking;
use App\Models\Dispute;
use App\Services\DisputeResolutionService;
use App\Services\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    private DisputeService $disputeService;

    private DisputeResolutionService $disputeResolutionService;

    public function __construct(DisputeService $disputeService, DisputeResolutionService $disputeResolutionService)
    {
        $this->disputeService = $disputeService;
        $this->disputeResolutionService = $disputeResolutionService;
    }

    /**
     * Create a dispute for a booking (buyer only)
     */
    public function store(DisputeRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (! $booking) {
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

    /**
     * List all disputes (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);

        $query = Dispute::orderBy('created_at', 'desc');
        $total = $query->count();
        $disputes = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'disputes' => $disputes->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ], 200);
    }

    /**
     * Resolve dispute (admin only)
     */
    public function resolve(DisputeResolutionRequest $request, int $id): JsonResponse
    {
        $dispute = Dispute::find($id);
        if (! $dispute) {
            return response()->json([
                'message' => 'Dispute not found',
            ], 404);
        }

        try {
            $dispute = $this->disputeResolutionService->resolve(
                $request->user(),
                $dispute,
                $request->validated()
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Dispute resolved successfully',
            'data' => $dispute,
        ], 200);
    }
}
