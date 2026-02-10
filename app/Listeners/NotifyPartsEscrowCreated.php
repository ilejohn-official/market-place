<?php

namespace App\Listeners;

use App\Events\EscrowCreated;

class NotifyPartsEscrowCreated
{
    public function handle(EscrowCreated $event): void
    {
        // Placeholder for notifying buyer and seller with fee breakdown.
    }
}
