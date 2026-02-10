<?php

namespace App\Services;

use App\Events\BookingCreated;
use App\Events\WorkMarkedComplete;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Event;

class BookingService
{
    /**
     * Create a new booking (buyer only)
     */
    public function createBooking(User $buyer, array $data): Booking
    {
        if (!$buyer->isBuyer()) {
            throw new Exception('Only buyers can create bookings');
        }

        $seller = User::find($data['seller_id']);
        if (!$seller || !$seller->isSeller()) {
            throw new Exception('Seller not found');
        }

        $service = Service::where('id', $data['service_id'])
            ->where('seller_id', $seller->id)
            ->first();

        if (!$service) {
            throw new Exception('Service not found');
        }

        $booking = Booking::create([
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'service_id' => $service->id,
            'agreed_amount' => $data['proposed_amount'],
            'status' => 'pending_negotiation',
            'negotiation_notes' => $data['negotiation_notes'] ?? null,
        ]);

        Event::dispatch(new BookingCreated($booking));

        return $booking;
    }

    /**
     * Get booking details for a participant
     */
    public function getBookingForUser(User $user, int $bookingId): Booking
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            throw new Exception('Booking not found');
        }

        if ($booking->buyer_id !== $user->id && $booking->seller_id !== $user->id) {
            throw new Exception('You are not authorized to view this booking');
        }

        return $booking;
    }

    /**
     * Get bookings for the authenticated buyer or seller
     */
    public function getUserBookings(User $user, int $page = 1, int $limit = 15, array $filters = []): array
    {
        if (!$user->isBuyer() && !$user->isSeller()) {
            throw new Exception('Invalid user role');
        }

        $query = Booking::query();

        if ($user->isBuyer()) {
            $query->where('buyer_id', $user->id);
        } else {
            $query->where('seller_id', $user->id);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

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
     * Mark work as complete (seller only)
     */
    public function markComplete(User $seller, Booking $booking): Booking
    {
        if (!$seller->isSeller() || $booking->seller_id !== $seller->id) {
            throw new Exception('You are not authorized to mark this booking complete');
        }

        if ($booking->status !== 'in_progress') {
            throw new Exception('Booking is not in progress');
        }

        $booking->update([
            'status' => 'pending_approval',
        ]);

        Event::dispatch(new WorkMarkedComplete($booking));

        return $booking;
    }
}
