<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Get current user's transactions
     */
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 15);

        $query = Transaction::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $transactions = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'transactions' => $transactions->items(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit),
        ], 200);
    }
}
