<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;

class WalletService
{
    public function getWallet(User $user): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'NGN']
        );
    }

    public function getBalance(User $user): Wallet
    {
        return $this->getWallet($user);
    }

    public function addFunds(User $user, float $amount): Wallet
    {
        if ($amount <= 0) {
            throw new Exception('Amount must be greater than 0');
        }

        return DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => 'NGN',
                ]);
            }

            $wallet->update([
                'balance' => $wallet->balance + $amount,
            ]);

            return $wallet;
        });
    }

    public function deductFunds(User $user, float $amount): Wallet
    {
        if ($amount <= 0) {
            throw new Exception('Amount must be greater than 0');
        }

        return DB::transaction(function () use ($user, $amount) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->first();
            if (! $wallet) {
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'balance' => 0,
                    'currency' => 'NGN',
                ]);
            }

            if ($wallet->balance < $amount) {
                throw new Exception('Insufficient wallet balance');
            }

            $wallet->update([
                'balance' => $wallet->balance - $amount,
            ]);

            return $wallet;
        });
    }
}
