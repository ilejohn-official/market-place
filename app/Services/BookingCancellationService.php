<?php

namespace App\Services;

use App\Events\BookingCancelled;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class BookingCancellationService
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function cancel(User $user, Booking $booking, ?string $reason = null): Booking
    {
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to cancel this booking');
        }

        if ($booking->status !== 'pending_negotiation') {
            throw new Exception('Booking cannot be cancelled at this stage');
        }

        return DB::transaction(function () use ($booking, $reason) {
            $escrow = $booking->escrowAccount;
            if ($escrow && $escrow->status === 'held') {
                $escrow->update([
                    'status' => 'refunded',
                ]);

                $this->walletService->addFunds($booking->buyer, (float) $escrow->total_amount);

                Transaction::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->buyer_id,
                    'transaction_type' => 'cancellation_refund',
                    'amount' => $escrow->total_amount,
                    'status' => 'completed',
                    'description' => $reason ? "Cancellation refund: {$reason}" : 'Cancellation refund',
                ]);
            }

            $booking->update([
                'status' => 'cancelled',
            ]);

            Event::dispatch(new BookingCancelled($booking));

            return $booking;
        });
    }
}
