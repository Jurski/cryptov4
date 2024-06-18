<?php

namespace App\Models;

use Carbon\Carbon;
use JsonSerializable;

class Transaction implements JsonSerializable
{
    private Carbon $date;
    private string $type;
    private float $amount;
    private string $cryptocurrency;
    private float $purchasePrice;

    public function __construct(Carbon $date, string $type, float $amount, string $cryptocurrency, float $purchasePrice)
    {
        $this->date = $date;
        $this->type = $type;
        $this->amount = $amount;
        $this->cryptocurrency = $cryptocurrency;
        $this->purchasePrice = $purchasePrice;
    }

    public function getDate(): Carbon
    {
        return $this->date;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCryptocurrency(): string
    {
        return $this->cryptocurrency;
    }

    public function getPurchasePrice(): float
    {
        return $this->purchasePrice;
    }


    public function getType(): string
    {
        return $this->type;
    }

    public function jsonSerialize(): array
    {
        return [
            "date" => $this->date,
            "type" => $this->type,
            "amount" => $this->amount,
            "cryptocurrency" => $this->cryptocurrency,
            "purchasePrice" => $this->purchasePrice
        ];
    }
}