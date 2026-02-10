<?php

namespace App\Events;

use App\Models\Booking;

class BookingCancelled
{
    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
}
