<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrenciesCollection;
use App\Services\CurrencyService;
use Illuminate\Http\Request;


class MainController extends Controller
{
    public function __construct(
        private CurrencyService $currenciesService,
    )
    {
    }

    public function __invoke(Request $request): CurrenciesCollection
    {
        $charCodes = $request->get('currencies');
        $latestCurrencies = $this
                ->currenciesService
                ->getCurrencies($charCodes);

        return new CurrenciesCollection($latestCurrencies);
    }
}
