<?php

require "vendor/autoload.php";

use App\Database;
use App\App;
use App\Services\WalletService;


$database = new Database();
$walletService = new WalletService($database);
$app = new App($walletService);


while (true) {
    $username = readline("Enter your username: ");
    $password = readline("Enter your password: ");

    $currentUser = $app->loadUser($username, $password);

    if ($currentUser) {
        echo "Welcome {$currentUser->getUsername()}\n";

        if (!$app->getWallet()) {
            $app->createWalletForUser($currentUser->getId());
        }

        $app->loadUserData($currentUser->getId());
        break;
    } else {
        echo "Invalid username or password.\n";
    }
}

$userOptions = [
    "1" => "List top cryptos",
    "2" => "Search by symbol",
    "3" => "Purchase crypto",
    "4" => "Sell crypto",
    "5" => "Show wallet state",
    "6" => "Show transaction history",
    "7" => "Exit",
];

while (true) {
    echo "Options:" . PHP_EOL;
    foreach ($userOptions as $option => $value) {
        echo "- $option: $value" . PHP_EOL;
    }

    $inputOption = trim(readline("Enter your choice: "));

    switch ($inputOption) {
        case "1":
            echo $app->listTopCryptos() ?? "No Cryptos found.";
            break;
        case "2":
            $userInput = trim(strtoupper(readline("Enter symbol to search: ")));

            echo $app->listSingleCrypto($userInput) ?? "No cryptocurrency found for symbol: " . $userInput;

            break;
        case "3":
            $symbol = trim(strtoupper(readline("Enter symbol to buy: ")));
            $amount = trim(readline("Enter amount to buy: "));

            if (!empty($symbol) && is_numeric($amount)) {
                $app->buyCrypto($symbol, (float)$amount);
            } else {
                echo "Invalid input." . PHP_EOL;
            }
            break;
        case "4":
            $symbol = trim(strtoupper(readline("Enter symbol to sell: ")));
            $amount = trim(readline("Enter amount to sell: "));

            if (!empty($symbol) && is_numeric($amount)) {
                $app->sellCrypto($symbol, (float)$amount);
            } else {
                echo "Invalid input." . PHP_EOL;
            }
            break;
        case "5":
            $app->displayWalletState();
            break;
        case "6":
            $app->displayTransactions();
            break;
        case "7":
            exit("Goodbye!");
        default:
            echo "Undefined option!" . PHP_EOL;
            break;
    }
}
