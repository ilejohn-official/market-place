<?php

namespace App\Listeners;

use App\Events\DisputeResolved;

class NotifyPartiesDisputeResolved
{
    public function handle(DisputeResolved $event): void
    {
        // Placeholder for notifying buyer and seller of resolution.
    }
}
