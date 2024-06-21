<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;

class SellService
{
    public function execute(Wallet $wallet, Cryptocurrency $cryptocurrency, float $amount): ?Transaction
    {
        $symbol = $cryptocurrency->getSymbol();
        $availableCrypto = $wallet->getHoldings()[$symbol] ?? 0;

        if ($availableCrypto >= $amount) {
            $price = $cryptocurrency->getPrice();
            $sellAmount = $amount * $price;

            $newBalance = $wallet->getBalanceUsd() + $sellAmount;
            $wallet->setBalanceUsd($newBalance);

            $updatedAvailableCrypto = $availableCrypto - $amount;
            if ($updatedAvailableCrypto <= 0) {
                $wallet->removeHolding($symbol);
            } else {
                $wallet->updateHolding($symbol, $updatedAvailableCrypto);
            }

            $transaction = new Transaction(
                Carbon::now("UTC"),
                "sell",
                $amount,
                $symbol,
                $price
            );
            $wallet->addTransaction($transaction);

            echo "Successfully sold {$transaction->getAmount()} {$transaction->getCryptocurrency()}\n";
            return $transaction;
        } else {
            echo "You do not have that amount of crypto to sell!\n";
        }

        return null;
    }
}