<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EscrowAccount;
use App\Models\User;
use Exception;

class BookingManagementService
{
    /**
     * Create a new booking (buyer only)
     */
    public function createBooking(User $buyer, array $data): Booking
    {
        if (!$buyer->isBuyer()) {
            throw new Exception('Only buyers can create bookings');
        }

        $booking = Booking::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $data['seller_id'],
            'service_id' => $data['service_id'],
            'agreed_amount' => $data['proposed_amount'],
            'status' => 'pending_negotiation',
            'negotiation_notes' => $data['description'] ?? null,
        ]);

        // Create escrow account for this booking
        EscrowAccount::create([
            'booking_id' => $booking->id,
            'total_amount' => $data['proposed_amount'],
            'platform_fee' => $data['proposed_amount'] * 0.10,
            'freelancer_amount' => $data['proposed_amount'] * 0.90,
            'status' => 'held',
        ]);

        return $booking;
    }

    /**
     * Get booking details
     */
    public function getBookingDetails(int $bookingId): ?Booking
    {
        return Booking::find($bookingId);
    }

    /**
     * Update booking status
     */
    public function updateBookingStatus(User $user, int $bookingId, string $status): Booking
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        // Only buyer or seller can update
        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to update this booking');
        }

        $booking->update(['status' => $status]);

        return $booking;
    }

    /**
     * Get buyer's bookings
     */
    public function getBuyerBookings(User $buyer, int $page = 1, int $limit = 15): array
    {
        if (!$buyer->isBuyer()) {
            throw new Exception('Only buyers have bookings');
        }

        $query = Booking::where('buyer_id', $buyer->id);
        $total = $query->count();
        $bookings = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'bookings' => $bookings->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }

    /**
     * Get seller's bookings
     */
    public function getSellerBookings(User $seller, int $page = 1, int $limit = 15): array
    {
        if (!$seller->isSeller()) {
            throw new Exception('Only sellers have bookings');
        }

        $query = Booking::where('seller_id', $seller->id);
        $total = $query->count();
        $bookings = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'bookings' => $bookings->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}
