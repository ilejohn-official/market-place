<?php

namespace App\Listeners;

use App\Events\BookingCreated;

class NotifySellerBookingCreated
{
    public function handle(BookingCreated $event): void
    {
        // Placeholder for seller notification logic.
    }
}
