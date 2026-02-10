<?php

namespace App\Services;

use App\Events\EscrowCreated;
use App\Models\Booking;
use App\Models\EscrowAccount;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class EscrowService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function createEscrow(User $buyer, Booking $booking, float $amount): EscrowAccount
    {
        if (! $buyer->isBuyer() || $booking->buyer_id !== $buyer->id) {
            throw new Exception('You are not authorized to create escrow for this booking');
        }

        return DB::transaction(function () use ($buyer, $booking, $amount) {
            $this->walletService->deductFunds($buyer, $amount);

            $platformFee = round($amount * 0.10, 2);
            $freelancerAmount = round($amount * 0.90, 2);

            $escrow = EscrowAccount::create([
                'booking_id' => $booking->id,
                'total_amount' => $amount,
                'platform_fee' => $platformFee,
                'freelancer_amount' => $freelancerAmount,
                'status' => 'held',
            ]);

            Transaction::create([
                'booking_id' => $booking->id,
                'user_id' => $buyer->id,
                'transaction_type' => 'escrow_hold',
                'amount' => $amount,
                'status' => 'completed',
                'description' => 'Escrow hold for booking',
            ]);

            $booking->update([
                'status' => 'in_progress',
                'agreed_amount' => $amount,
            ]);

            Event::dispatch(new EscrowCreated($escrow));

            return $escrow;
        });
    }

    public function getEscrow(User $user, Booking $booking): ?EscrowAccount
    {
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to view escrow for this booking');
        }

        return $booking->escrowAccount;
    }
}
