<?php

namespace App\Api;

use App\Models\Cryptocurrency;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CryptoCompareApi implements CryptoApi
{
    private Client $client;
    private string $apiKey = "";

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://min-api.cryptocompare.com/data/',
            'timeout' => 5.0,
        ]);
    }

    public function getTopCryptos(): array
    {
        try {
            $response = $this->client->request('GET', 'top/totalvolfull', [
                'query' => [
                    'limit' => 10,
                    'tsym' => 'USD',
                    'api_key' => $this->apiKey,
                ]
            ]);

            $apiData = json_decode($response->getBody(), true);


            $currencies = [];

            foreach ($apiData['Data'] as $crypto) {
                if (isset($crypto['RAW']['USD']['PRICE'])) {
                    $currencies[] = new Cryptocurrency(
                        $crypto['CoinInfo']['FullName'],
                        $crypto['CoinInfo']['Name'],
                        $crypto['RAW']['USD']['PRICE']
                    );
                } else {
                    echo "Skipping cryptocurrency due to missing price data: " . $crypto['CoinInfo']['Name'] . PHP_EOL;
                }
            }

            return $currencies;

        } catch (GuzzleException $e) {
            echo "Guzzle error: " . $e->getMessage();
            return [];
        } catch (\Exception $e) {
            echo "General error: " . $e->getMessage();
            return [];
        }
    }

    public function getCryptoBySymbol(string $symbol): ?array
    {
        try {
            $response = $this->client->request('GET', 'price', [
                'query' => [
                    'fsym' => strtoupper($symbol),
                    'tsyms' => 'USD',
                    'api_key' => $this->apiKey,
                ]
            ]);

            $apiData = json_decode($response->getBody(), true);

            if (isset($apiData['USD'])) {
                return [
                    new Cryptocurrency($symbol, $symbol, $apiData['USD'])
                ];
            } else {
                return null;
            }

        } catch (GuzzleException $e) {
            echo "Guzzle error: " . $e->getMessage();
            return [];
        } catch (\Exception $e) {
            echo "General error: " . $e->getMessage();
            return [];
        }
    }
}

