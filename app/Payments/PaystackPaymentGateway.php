<?php

namespace App\Payments;

use App\Contracts\PaymentGatewayInterface;

class PaystackPaymentGateway implements PaymentGatewayInterface
{
    public function charge(array $payload): array
    {
        return [
            'status' => 'success',
            'reference' => 'paystack_charge_mock',
            'payload' => $payload,
        ];
    }

    public function refund(array $payload): array
    {
        return [
            'status' => 'success',
            'reference' => 'paystack_refund_mock',
            'payload' => $payload,
        ];
    }

    public function webhook(array $payload): array
    {
        return [
            'status' => 'received',
            'payload' => $payload,
        ];
    }
}
