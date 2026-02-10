<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    public function charge(array $payload): array;

    public function refund(array $payload): array;

    public function webhook(array $payload): array;
}
