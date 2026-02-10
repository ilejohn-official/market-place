<?php

namespace App\Http\Controllers;

use App\Http\Requests\CallRequest;
use App\Models\Booking;
use App\Models\Call;
use App\Services\CallService;
use Illuminate\Http\JsonResponse;

class CallController extends Controller
{
    private CallService $callService;

    public function __construct(CallService $callService)
    {
        $this->callService = $callService;
    }

    /**
     * Initiate a call in a booking
     */
    public function store(CallRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (! $booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $result = $this->callService->initiateCall(
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
            'message' => 'Call initiated successfully',
            'data' => $result,
        ], 201);
    }

    /**
     * Update call status
     */
    public function update(CallRequest $request, int $id): JsonResponse
    {
        $call = Call::find($id);
        if (! $call) {
            return response()->json([
                'message' => 'Call not found',
            ], 404);
        }

        try {
            $call = $this->callService->updateStatus(
                $request->user(),
                $call,
                $request->validated()
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Call updated successfully',
            'data' => $call,
        ], 200);
    }
}
