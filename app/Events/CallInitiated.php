<?php

namespace App\Events;

use App\Models\Call;

class CallInitiated
{
    public Call $call;

    public string $token;

    public function __construct(Call $call, string $token)
    {
        $this->call = $call;
        $this->token = $token;
    }
}
