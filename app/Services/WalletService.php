<?php

namespace App\Services;

use App\Database;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;

class WalletService
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }


    public function getDatabase(): Database
    {
        return $this->database;
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

    public function saveTransaction(Transaction $transaction, int $walletId): void
    {
        $stmt = $this->database->prepare("INSERT OR REPLACE INTO transactions (
                wallet_id,
                date,
                type,
                amount,
                cryptocurrency,
                purchase_price
            ) VALUES (
                :wallet_id,
                :date,
                :type,
                :amount,
                :cryptocurrency,
                :purchase_price
            )");

        $stmt->bindValue(':wallet_id', $walletId, SQLITE3_INTEGER);
        $stmt->bindValue(':date', $transaction->getDate(), SQLITE3_TEXT);
        $stmt->bindValue(':type', $transaction->getType(), SQLITE3_TEXT);
        $stmt->bindValue(':amount', $transaction->getAmount(), SQLITE3_FLOAT);
        $stmt->bindValue(':cryptocurrency', $transaction->getCryptocurrency(), SQLITE3_TEXT);
        $stmt->bindValue(':purchase_price', $transaction->getPurchasePrice(), SQLITE3_FLOAT);

        $stmt->execute();
    }

    public function loadTransactions(int $walletId): array
    {
        $stmt = $this->database->prepare("SELECT * FROM transactions WHERE wallet_id = :walletId");

        $stmt->bindValue(':walletId', $walletId, SQLITE3_INTEGER);

        $result = $stmt->execute();

        $transactions = [];

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $transactions[] = new Transaction(
                Carbon::parse($row["date"]),
                $row["type"],
                $row["amount"],
                $row["cryptocurrency"],
                $row["purchase_price"]
            );
        }

        return $transactions;
    }
}
