<?php

namespace App\Listeners;

use App\Events\BookingCancelled;

class NotifyPartiesBookingCancelled
{
    public function handle(BookingCancelled $event): void
    {
        // Placeholder for notifying buyer and seller.
    }
}
