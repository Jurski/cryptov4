<?php

namespace App\Models;

use Carbon\Carbon;
use JsonSerializable;

class Wallet implements JsonSerializable
{
    private int $walletId;
    private int $userId;
    private float $balanceUsd = 0;
    private array $holdings = [];
    private array $transactions = [];


    public function __construct(float $balanceUsd = 1000.00)
    {
        $this->balanceUsd = $balanceUsd;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }


    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }


    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function setWalletId(int $walletId): void
    {
        $this->walletId = $walletId;
    }


    public function setBalanceUsd(float $balanceUsd): void
    {
        $this->balanceUsd = $balanceUsd;
    }

    public function getBalanceUsd(): float
    {
        return $this->balanceUsd;
    }


    public function setHoldings(array $holdings): void
    {
        $this->holdings = $holdings;
    }

    public function getHoldings(): array
    {
        return $this->holdings;
    }

    public function setTransactions(array $transactions): void
    {
        $this->transactions = $transactions;
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function buyCrypto(Cryptocurrency $cryptocurrency, float $amount): ?Transaction
    {
        $symbol = $cryptocurrency->getSymbol();
        $price = $cryptocurrency->getPrice();

        $totalCost = $amount * $price;

        if ($totalCost <= $this->balanceUsd) {
            $newBalance = $this->balanceUsd - $totalCost;

            $this->setBalanceUsd($newBalance);

            $currentHoldings = $this->getHoldings();

            if (isset($currentHoldings[$symbol])) {
                $currentHoldings[$symbol] += $amount;
            } else {
                $currentHoldings[$symbol] = $amount;
            }

            $this->setHoldings($currentHoldings);

            $transaction = new Transaction(
                Carbon::now('UTC'),
                'purchase',
                $amount,
                $symbol,
                $price
            );

            $this->transactions[] = $transaction;

            echo "Succesfuly bought {$transaction->getAmount()} {$transaction->getCryptocurrency()}\n";

            return $transaction;
        } else {
            echo "Not enough money for purhcase!";
        }

        return null;
    }

    public function sellCrypto(Cryptocurrency $cryptocurrency, float $amount): ?Transaction
    {
        $symbol = $cryptocurrency->getSymbol();
        $availableCrypto = $this->holdings[$symbol] ?? 0;

        if ($availableCrypto >= $amount) {
            $price = $cryptocurrency->getPrice();

            $sellAmount = $amount * $price;

            $currentBalance = $this->getBalanceUsd();
            $newBalance = $currentBalance + $sellAmount;

            $this->setBalanceUsd($newBalance);

            $updatedAvailableCrypto = $availableCrypto - $amount;
            if ($updatedAvailableCrypto <= 0) {
                unset($this->holdings[$symbol]);
            } else {
                $this->holdings[$symbol] = $updatedAvailableCrypto;
            }

            $transaction = new Transaction(
                Carbon::now("UTC"),
                "sell",
                $amount,
                $symbol,
                $price
            );
            $this->transactions[] = $transaction;

            echo "Succesfuly sold {$transaction->getAmount()} {$transaction->getCryptocurrency()}\n";

            return $transaction;
        } else {
            echo "You do not have that amount of crypto to sell!" . PHP_EOL;
        }
        return null;
    }

    public function jsonSerialize(): array
    {
        return [
            "balanceUsd" => $this->balanceUsd,
            "holdings" => $this->holdings,
            "transactions" => $this->transactions
        ];
    }
}