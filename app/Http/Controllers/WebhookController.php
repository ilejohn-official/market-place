<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    private PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Receive payment webhook
     */
    public function payment(Request $request): JsonResponse
    {
        $result = $this->paymentService->webhook($request->all());

        return response()->json($result, 200);
    }
}
