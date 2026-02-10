<?php

namespace App\Listeners;

use App\Events\WorkMarkedComplete;

class NotifyBuyerWorkComplete
{
    public function handle(WorkMarkedComplete $event): void
    {
        // Placeholder for notifying buyer.
    }
}
