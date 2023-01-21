<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CurrenciesFetchService
{
    public function __construct(
        private Client $guzzle
    )
    {
    }

    /**
     * Сделать запрос к эндпоинту цб
     * @return string Результирующий xml
     *
     * @throws GuzzleException
     */
    public function fetch(): string
    {
        $response = $this->guzzle->get(
            config('currencies.cbr_endpoint')
        );
        $xml = $response->getBody()->getContents();

        return $xml;
    }
}
