<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class DisputeService
{
    public function createDispute(User $buyer, Booking $booking, array $data): Dispute
    {
        if (!$buyer->isBuyer() || $booking->buyer_id !== $buyer->id) {
            throw new Exception('You are not authorized to dispute this booking');
        }

        if (!in_array($booking->status, ['in_progress', 'pending_approval'], true)) {
            throw new Exception('Booking cannot be disputed in its current status');
        }

        return DB::transaction(function () use ($buyer, $booking, $data) {
            $dispute = Dispute::create([
                'booking_id' => $booking->id,
                'created_by_id' => $buyer->id,
                'reason' => $data['reason'],
                'description' => $data['description'] ?? null,
                'evidence_attachments' => $data['evidence_attachments'] ?? [],
                'status' => 'open',
            ]);

            if ($booking->escrowAccount) {
                $booking->escrowAccount->update([
                    'status' => 'frozen',
                ]);
            }

            $booking->update([
                'status' => 'disputed',
            ]);

            return $dispute;
        });
    }
}
