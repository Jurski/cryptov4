<?php

namespace App\Repositories;

use App\Database;
use App\Models\Transaction;
use Carbon\Carbon;

class TransactionRepository
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
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


