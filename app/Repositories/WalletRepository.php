<?php

namespace App\Repositories;

use App\Database;
use App\Models\Wallet;

class WalletRepository
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function loadWalletByUserId(int $userId): ?array
    {
        $stmt = $this->database->prepare("SELECT * FROM wallets WHERE user_id = :userId");
        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $result = $stmt->execute();

        $wallet = $result->fetchArray(SQLITE3_ASSOC);

        return $wallet ?: null;
    }

    public function createWalletForDB(int $userId, float $balance = 1000, array $holdings = []): void
    {
        if ($this->loadWalletByUserId($userId) !== null) {
            return;
        }

        $stmt = $this->database->prepare("INSERT INTO wallets (user_id, balance_usd, holdings)
                                    VALUES (:userId, :balanceUsd, :holdings)");

        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':balanceUsd', $balance, SQLITE3_FLOAT);
        $stmt->bindValue(':holdings', json_encode($holdings), SQLITE3_TEXT);

        $stmt->execute();
    }

    public function updateWallet(int $userId, float $balanceUsd, array $holdings): void
    {
        $query = "UPDATE wallets SET balance_usd = :balanceUsd, holdings = :holdings WHERE user_id = :userId";
        $stmt = $this->database->prepare($query);

        $stmt->bindValue(':userId', $userId, SQLITE3_INTEGER);
        $stmt->bindValue(':balanceUsd', $balanceUsd, SQLITE3_FLOAT);
        $stmt->bindValue(':holdings', json_encode($holdings), SQLITE3_TEXT);
        $stmt->execute();
    }


    public function loadWallet(int $userId): Wallet
    {
        $result = $this->loadWalletByUserId($userId);

        if ($result) {
            $wallet = new Wallet();
            $wallet->setWalletId($result["id"]);
            $wallet->setUserId($result["user_id"]);
            $wallet->setBalanceUsd((float)$result["balance_usd"]);
            $wallet->setHoldings(json_decode($result["holdings"], true));
            return $wallet;
        }

        return new Wallet();
    }
}
