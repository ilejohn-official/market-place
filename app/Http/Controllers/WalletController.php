<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get wallet balance
     */
    public function show(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getBalance($request->user());

        return response()->json([
            'data' => $wallet,
        ], 200);
    }
}
