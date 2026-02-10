<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Booking;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private MessageService $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    /**
     * Send a message in a booking
     */
    public function store(MessageRequest $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $message = $this->messageService->sendMessage(
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
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    /**
     * Get messages in a booking
     */
    public function index(Request $request, int $id): JsonResponse
    {
        $booking = Booking::find($id);
        if (!$booking) {
            return response()->json([
                'message' => 'Booking not found',
            ], 404);
        }

        try {
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 15);
            $result = $this->messageService->getMessages($request->user(), $booking, $page, $limit);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json($result, 200);
    }

    /**
     * Mark message as read
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $message = Message::find($id);
        if (!$message) {
            return response()->json([
                'message' => 'Message not found',
            ], 404);
        }

        try {
            $message = $this->messageService->markAsRead($request->user(), $message);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json([
            'message' => 'Message marked as read',
            'data' => $message,
        ], 200);
    }
}
