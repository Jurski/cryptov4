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

    public function addTransaction(Transaction $transaction): void
    {
        $this->transactions[] = $transaction;
    }

    public function removeHolding(string $symbol): void
    {
        unset($this->holdings[$symbol]);
    }

    public function updateHolding(string $symbol, float $amount): void
    {
        if ($amount > 0) {
            $this->holdings[$symbol] = $amount;
        } else {
            $this->removeHolding($symbol);
        }
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