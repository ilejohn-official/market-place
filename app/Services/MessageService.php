<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Message;
use App\Models\User;
use Exception;

class MessageService
{
    public function sendMessage(User $user, Booking $booking, array $data): Message
    {
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to send messages for this booking');
        }

        return Message::create([
            'booking_id' => $booking->id,
            'sender_id' => $user->id,
            'message_content' => $data['message_content'],
            'file_attachment_url' => $data['file_attachment_url'] ?? null,
            'is_read' => false,
        ]);
    }

    public function getMessages(User $user, Booking $booking, int $page = 1, int $limit = 15): array
    {
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to view messages for this booking');
        }

        $query = Message::where('booking_id', $booking->id)
            ->orderBy('created_at', 'asc');

        $total = $query->count();
        $messages = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'messages' => $messages->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    public function markAsRead(User $user, Message $message): Message
    {
        $booking = $message->booking;
        if (!$booking) {
            throw new Exception('Booking not found');
        }

        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to update this message');
        }

        if ($message->sender_id === $user->id) {
            throw new Exception('You cannot mark your own message as read');
        }

        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return $message;
    }
}
