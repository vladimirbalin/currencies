<?php

namespace App\ThirdParty;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CBR
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
    public function fetchCurrencies(): string
    {
        $response = $this->guzzle->get(
            config('currencies.cbr_endpoint')
        );
        $xml = $response->getBody()->getContents();

        return $xml;
    }
}
