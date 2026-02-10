<?php

namespace App\Listeners;

use App\Events\FundsReleased;

class NotifySellerFundsReleased
{
    public function handle(FundsReleased $event): void
    {
        // Placeholder for notifying seller.
    }
}
