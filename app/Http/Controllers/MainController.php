<?php

namespace App\Http\Controllers;

use App\Http\Resources\CurrenciesCollection;
use App\Repositories\CurrencyRepository;
use App\Services\CurrencyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;


class MainController extends Controller
{
    public function __construct(
        private CurrencyService    $currenciesService,
        private CurrencyRepository $currenciesRepository
    )
    {
    }

    /**
     * @throws GuzzleException
     */
    public function updateDb(): void
    {
        $this->currenciesService
            ->fetchExchangeRatesToXmlFile();
        $this->currenciesService
            ->updateOrCreateExchangeRatesInDb();
    }

    public function currencies(Request $request)
    {
        $charCodes = $request->get('currencies');
        $latestCurrencies = $this
            ->currenciesService
            ->getCurrencies($charCodes);

        return new CurrenciesCollection($latestCurrencies);
    }
}
