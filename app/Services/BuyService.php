<?php

namespace App\Services;

use App\Models\Cryptocurrency;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;

class BuyService
{
    public function execute(Wallet $wallet, Cryptocurrency $cryptocurrency, float $amount): ?Transaction
    {
        $symbol = $cryptocurrency->getSymbol();
        $price = $cryptocurrency->getPrice();

        $totalCost = $amount * $price;

        if ($totalCost <= $wallet->getBalanceUsd()) {
            $newBalance = $wallet->getBalanceUsd() - $totalCost;

            $wallet->setBalanceUsd($newBalance);

            $currentHoldings = $wallet->getHoldings();

            if (isset($currentHoldings[$symbol])) {
                $currentHoldings[$symbol] += $amount;
            } else {
                $currentHoldings[$symbol] = $amount;
            }

            $wallet->setHoldings($currentHoldings);

            $transaction = new Transaction(
                Carbon::now('UTC'),
                'purchase',
                $amount,
                $symbol,
                $price
            );

            $wallet->addTransaction($transaction);

            echo "Succesfuly bought {$transaction->getAmount()} {$transaction->getCryptocurrency()}\n";

            return $transaction;
        } else {
            echo "Not enough money for purhcase!";
        }

        return null;
    }
}