<?php

namespace App\Events;

use App\Models\EscrowAccount;

class EscrowCreated
{
    public EscrowAccount $escrow;

    public function __construct(EscrowAccount $escrow)
    {
        $this->escrow = $escrow;
    }
}
