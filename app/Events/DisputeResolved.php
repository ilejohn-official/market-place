<?php

namespace App\Events;

use App\Models\Dispute;

class DisputeResolved
{
    public Dispute $dispute;

    public function __construct(Dispute $dispute)
    {
        $this->dispute = $dispute;
    }
}
