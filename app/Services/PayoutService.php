<?php

namespace App\Services;

use App\Events\FundsReleased;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class PayoutService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function releaseFunds(User $buyer, Booking $booking): Booking
    {
        if (!$buyer->isBuyer() || $booking->buyer_id !== $buyer->id) {
            throw new Exception('You are not authorized to approve this booking');
        }

        if ($booking->status !== 'pending_approval') {
            throw new Exception('Booking is not awaiting approval');
        }

        $escrow = $booking->escrowAccount;
        if (!$escrow || $escrow->status !== 'held') {
            throw new Exception('Escrow is not available for release');
        }

        return DB::transaction(function () use ($buyer, $booking, $escrow) {
            $escrow->update([
                'status' => 'released',
                'released_at' => now(),
            ]);

            $seller = $booking->seller;
            $this->walletService->addFunds($seller, (float) $escrow->freelancer_amount);

            Transaction::create([
                'booking_id' => $booking->id,
                'user_id' => $seller->id,
                'transaction_type' => 'payout',
                'amount' => $escrow->freelancer_amount,
                'status' => 'completed',
                'description' => 'Payout to seller',
            ]);

            Transaction::create([
                'booking_id' => $booking->id,
                'user_id' => $buyer->id,
                'transaction_type' => 'platform_fee',
                'amount' => $escrow->platform_fee,
                'status' => 'completed',
                'description' => 'Platform fee',
            ]);

            $booking->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Event::dispatch(new FundsReleased($booking));

            return $booking;
        });
    }
}
