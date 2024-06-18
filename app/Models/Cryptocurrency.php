<?php

namespace App\Models;

class Cryptocurrency
{
    private string $name;
    private string $symbol;
    private float $price;

    public function __construct(string $name, string $symbol, float $price)
    {
        $this->name = $name;
        $this->symbol = $symbol;
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}