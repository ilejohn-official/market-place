<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;

class PaymentService
{
    private PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function charge(array $payload): array
    {
        return $this->gateway->charge($payload);
    }

    public function refund(array $payload): array
    {
        return $this->gateway->refund($payload);
    }

    public function webhook(array $payload): array
    {
        return $this->gateway->webhook($payload);
    }
}
