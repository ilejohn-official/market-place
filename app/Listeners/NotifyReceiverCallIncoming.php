<?php

namespace App\Listeners;

use App\Events\CallInitiated;

class NotifyReceiverCallIncoming
{
    public function handle(CallInitiated $event): void
    {
        // Placeholder for receiver notification logic.
    }
}
