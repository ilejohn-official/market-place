<?php

namespace App\Services;

use App\Events\DisputeResolved;
use App\Models\Dispute;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class DisputeResolutionService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function resolve(User $admin, Dispute $dispute, array $data): Dispute
    {
        if ($admin->role !== 'admin') {
            throw new Exception('Admin access required');
        }

        if ($dispute->status !== 'open') {
            throw new Exception('Dispute is already resolved');
        }

        $booking = $dispute->booking;
        $escrow = $booking ? $booking->escrowAccount : null;

        if (!$booking || !$escrow) {
            throw new Exception('Dispute booking or escrow not found');
        }

        return DB::transaction(function () use ($admin, $dispute, $booking, $escrow, $data) {
            $decision = $data['resolution_decision'];

            if ($decision === 'refund_to_buyer') {
                $escrow->update([
                    'status' => 'refunded',
                ]);

                $this->walletService->addFunds($booking->buyer, (float) $escrow->total_amount);

                Transaction::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->buyer_id,
                    'transaction_type' => 'refund',
                    'amount' => $escrow->total_amount,
                    'status' => 'completed',
                    'description' => 'Refund to buyer',
                ]);

                $booking->update([
                    'status' => 'refunded',
                ]);
            } else {
                $escrow->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);

                $this->walletService->addFunds($booking->seller, (float) $escrow->freelancer_amount);

                Transaction::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->seller_id,
                    'transaction_type' => 'payout',
                    'amount' => $escrow->freelancer_amount,
                    'status' => 'completed',
                    'description' => 'Payout to seller',
                ]);

                Transaction::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->buyer_id,
                    'transaction_type' => 'platform_fee',
                    'amount' => $escrow->platform_fee,
                    'status' => 'completed',
                    'description' => 'Platform fee',
                ]);

                $booking->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }

            $dispute->update([
                'status' => 'resolved',
                'resolution_decision' => $decision,
                'resolution_notes' => $data['notes'] ?? null,
                'resolved_by' => $admin->id,
                'resolved_at' => now(),
            ]);

            Event::dispatch(new DisputeResolved($dispute));

            return $dispute;
        });
    }
}
