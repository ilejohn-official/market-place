<?php

namespace App\Services;

use App\Models\Call;

class CallTokenService
{
    public function generateToken(Call $call): string
    {
        return sprintf('call_%d_%s', $call->id, bin2hex(random_bytes(8)));
    }
}
