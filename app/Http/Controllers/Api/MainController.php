<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrenciesRequest;
use App\Http\Resources\CurrenciesCollection;
use App\Services\Currency\CurrencyService;

class MainController extends Controller
{
    public function __construct(
        private CurrencyService $service
    )
    {
    }

    public function __invoke(
        CurrenciesRequest $request
    ): CurrenciesCollection
    {
        $charCodes = $request['currencies'];

        $latestComparedCurrencies = $this
            ->service
            ->getLatestWithStatus($charCodes);

        return new CurrenciesCollection($latestComparedCurrencies);
    }
}
