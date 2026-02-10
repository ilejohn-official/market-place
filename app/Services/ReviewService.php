<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Review;
use App\Models\SellerProfile;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class ReviewService
{
    public function createReview(User $buyer, Booking $booking, array $data): Review
    {
        if (! $buyer->isBuyer() || $booking->buyer_id !== $buyer->id) {
            throw new Exception('You are not authorized to review this booking');
        }

        if ($booking->status !== 'completed') {
            throw new Exception('Booking is not completed');
        }

        if (Review::where('booking_id', $booking->id)->exists()) {
            throw new Exception('Review already exists for this booking');
        }

        return DB::transaction(function () use ($booking, $buyer, $data) {
            $review = Review::create([
                'booking_id' => $booking->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $booking->seller_id,
                'rating' => $data['rating'],
                'review_text' => $data['review_text'] ?? null,
            ]);

            $average = Review::where('seller_id', $booking->seller_id)->avg('rating');
            $profile = SellerProfile::firstOrCreate(
                ['user_id' => $booking->seller_id],
                ['hourly_rate' => 0, 'experience_level' => 'beginner']
            );
            $profile->update([
                'rating' => $average ?? 0,
            ]);

            return $review;
        });
    }

    public function getSellerReviews(int $sellerId, int $page = 1, int $limit = 15): array
    {
        $query = Review::where('seller_id', $sellerId)->orderBy('created_at', 'desc');
        $total = $query->count();
        $reviews = $query->paginate($limit, ['*'], 'page', $page);

        return [
            'reviews' => $reviews->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ];
    }
}
