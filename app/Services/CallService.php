<?php

namespace App\Services;

use App\Events\CallInitiated;
use App\Models\Booking;
use App\Models\Call;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Event;

class CallService
{
    private CallTokenService $tokenService;

    public function __construct(CallTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function initiateCall(User $user, Booking $booking, array $data): array
    {
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to call for this booking');
        }

        $receiverId = $booking->buyer_id === $user->id
            ? $booking->seller_id
            : $booking->buyer_id;

        $call = Call::create([
            'booking_id' => $booking->id,
            'initiator_id' => $user->id,
            'receiver_id' => $receiverId,
            'call_type' => $data['call_type'],
            'status' => 'initiated',
            'started_at' => now(),
        ]);

        $token = $this->tokenService->generateToken($call);

        Event::dispatch(new CallInitiated($call, $token));

        return [
            'call' => $call,
            'initiator_token' => $token,
        ];
    }

    public function updateStatus(User $user, Call $call, array $data): Call
    {
        if ($call->initiator_id !== $user->id && $call->receiver_id !== $user->id) {
            throw new Exception('You are not authorized to update this call');
        }

        $status = $data['status'];

        $updates = ['status' => $status];

        if ($status === 'accepted' && ! $call->started_at) {
            $updates['started_at'] = now();
        }

        if ($status === 'completed') {
            $endedAt = now();
            $updates['ended_at'] = $endedAt;
            $updates['duration_seconds'] = $call->started_at
                ? $endedAt->diffInSeconds($call->started_at)
                : 0;
        }

        $call->update($updates);

        return $call;
    }
}
