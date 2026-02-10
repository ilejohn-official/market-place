<?php

return [
    'gateway' => env('PAYMENT_GATEWAY', 'paystack'),
    'paystack' => [
        'key' => env('PAYSTACK_KEY'),
        'secret' => env('PAYSTACK_SECRET'),
    ],
];
