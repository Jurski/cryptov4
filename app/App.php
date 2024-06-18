<?php

namespace App;

use App\Api\CmcApi;
use App\Api\CryptoApi;
use App\Models\Cryptocurrency;
use App\Models\User;
use App\Models\Wallet;
use App\Services\WalletService;
use Carbon\Carbon;
use InitPHP\CLITable\Table;

class App
{
    private CryptoApi $api;
    private WalletService $walletService;
    private ?Wallet $wallet = null;

    public function __construct(WalletService $walletService)
    {
        $this->api = new CmcApi();
        $this->walletService = $walletService;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    private function fillTable(array $cryptocurrencies): ?Table
    {
        if (count($cryptocurrencies) > 0) {
            $table = new Table();
            foreach ($cryptocurrencies as $cryptocurrency) {
                $table->row([
                    'name' => $cryptocurrency->getName(),
                    'symbol' => $cryptocurrency->getSymbol(),
                    'price' => number_format($cryptocurrency->getPrice(), 2) . " $",
                ]);
            }
            return $table;
        }
        return null;
    }

    public function listTopCryptos(): ?Table
    {
        $apiData = $this->api->getTopCryptos();
        return $this->fillTable($apiData);
    }

    public function listSingleCrypto(string $userInput): ?Table
    {
        $apiData = $this->api->getCryptoBySymbol($userInput);
        return $this->fillTable($apiData);
    }

    public function loadUser(string $username, string $password): ?User
    {
        $database = $this->walletService->getDatabase();
        $user = $database->loadUser($username);

        if ($user && md5($password) === $user["password"]) {
            return new User($user["id"], $user["username"], $user["password"]);
        }

        return null;
    }

    public function loadUserData(int $userId): void
    {
        $this->wallet = $this->walletService->loadWallet($userId);
        $transactions = $this->walletService->loadTransactions($this->wallet->getWalletId());

        $this->wallet->setTransactions($transactions);
    }

    public function createWalletForUser(int $userId): void
    {
        $this->walletService->createWalletForDB($userId);
        $this->loadUserData($userId);
    }

    public function buyCrypto(string $symbol, float $amount): void
    {
        if (!$this->wallet) {
            return;
        }

        $result = $this->api->getCryptoBySymbol($symbol);

        if ($result) {
            $cryptocurrency = new Cryptocurrency(
                $result[0]->getName(),
                $symbol,
                $result[0]->getPrice()
            );

            $transaction = $this->wallet->buyCrypto($cryptocurrency, $amount);

            if ($transaction) {
                $this->walletService->updateWallet(
                    $this->wallet->getUserId(),
                    $this->wallet->getBalanceUsd(),
                    $this->wallet->getHoldings()
                );
                $this->walletService->saveTransaction(
                    $transaction,
                    $this->wallet->getWalletId()
                );
            }
        } else {
            echo "No such crypto!";
        }
    }

    public function sellCrypto(string $symbol, float $amount): void
    {
        $result = $this->api->getCryptoBySymbol($symbol);

        if ($result) {
            $cryptocurrency = new Cryptocurrency(
                $result[0]->getName(),
                $symbol,
                $result[0]->getPrice()
            );
            $transaction = $this->wallet->sellCrypto(
                $cryptocurrency,
                $amount
            );

            if ($transaction) {
                $this->walletService->updateWallet(
                    $this->wallet->getUserId(),
                    $this->wallet->getBalanceUsd(),
                    $this->wallet->getHoldings()
                );
                $this->walletService->saveTransaction(
                    $transaction,
                    $this->wallet->getWalletId()
                );
            }
        }
    }

    public function displayWalletState(): void
    {
        if (!$this->wallet) {
            return;
        }

        $cash = $this->wallet->getBalanceUsd();
        $cashFormatted = number_format($cash, 2);

        echo "Cash balance - " . $cashFormatted . "$" . PHP_EOL;

        $holdings = $this->wallet->getHoldings();

        if (empty($holdings)) {
            echo "No holdings to display." . PHP_EOL;
            return;
        }

        $table = new Table();

        $totalCurrentValue = 0;

        foreach ($holdings as $symbol => $amount) {
            $apiData = $this->api->getCryptoBySymbol($symbol);
            $price = $apiData[0]->getPrice();
            $currentValue = $price * $amount;
            $totalCurrentValue += $currentValue;
            $transactions = $this->findAllTransactionsBySymbol($symbol);

            $totalPurchaseValue = 0;

            foreach ($transactions as $transaction) {
                $purchasePrice = $transaction->getPurchasePrice();
                $totalPurchaseValue += $purchasePrice * $transaction->getAmount();
            }

            $profit = $currentValue - $totalPurchaseValue;

            $table->row([
                'name' => $symbol,
                'amount' => $amount,
                'value' => number_format($currentValue, 2) . " $",
                'profit' => number_format($profit, 2) . " $",
            ]);
        }

        $totalBalance = $cash + $totalCurrentValue;
        $totalBalanceFormatted = number_format($totalBalance, 2);

        echo "Total balance - " . $totalBalanceFormatted . "$" . PHP_EOL;
        echo $table;
    }

    private function findAllTransactionsBySymbol(string $symbol): array
    {
        $transactions = $this->wallet->getTransactions();
        $filteredTransactions = [];

        foreach ($transactions as $transaction) {
            if ($transaction->getCryptocurrency() === $symbol) {
                $filteredTransactions[] = $transaction;
            }
        }

        return $filteredTransactions;
    }


    public function displayTransactions(): void
    {
        if (!$this->wallet) {
            return;
        }

        $transactions = $this->wallet->getTransactions();

        if (!$transactions) {
            echo "No transactions found" . PHP_EOL;
            return;
        }

        $table = new Table();

        foreach ($transactions as $transaction) {
            $dateUTC = Carbon::createFromFormat('Y-m-d H:i:s', $transaction->getDate(), 'UTC');
            $formattedDate = $dateUTC->setTimezone('Europe/Riga')->format('Y-m-d H:i:s');

            $table->row([
                'date' => $formattedDate,
                'type' => $transaction->getType(),
                'amount' => $transaction->getAmount(),
                'cryptocurrency' => $transaction->getCryptocurrency(),
                'price' => number_format($transaction->getPurchasePrice(), 2) . " $",
            ]);
        }

        echo $table;
    }
}
