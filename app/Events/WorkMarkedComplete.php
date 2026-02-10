<?php

namespace App\Events;

use App\Models\Booking;

class WorkMarkedComplete
{
    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }
}
