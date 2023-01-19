<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class CurrenciesDownloadService
{
    public function __construct(
        private Client $guzzle
    )
    {
    }

    /**
     * Сделать запрос к эндпоинту цб
//     * @return Результирующий xml
     *
     * @throws GuzzleException
     */
    public function fetch()
    {
        $response = $this->guzzle->get(
            config('currencies.cbr_endpoint')
        );
        $xml = $response->getBody()->getContents();

        return $xml;
    }
}
