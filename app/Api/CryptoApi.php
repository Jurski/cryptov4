<?php

namespace App\Api;

interface CryptoApi
{
    public function getTopCryptos(): array;

    public function getCryptoBySymbol(string $symbol): ?array;
}