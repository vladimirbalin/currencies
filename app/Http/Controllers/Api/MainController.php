<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrenciesRequest;
use App\Http\Resources\CurrenciesCollection;
use App\Repositories\CurrencyRepository;
use App\Services\CurrencyService;
use Illuminate\Http\Request;


class MainController extends Controller
{
    public function __construct(
        private CurrencyService    $service,
        private CurrencyRepository $repository
    )
    {
    }

    public function __invoke(
        CurrenciesRequest $request
    ): CurrenciesCollection
    {
        $charCodes = $request['currencies'];

        $latest = $this->repository->getAllLatest($charCodes);
        $prevLatest = $this->repository->getAllPrevLatest($charCodes);

        $latestComparedCurrencies = $this
                ->service
                ->comparedLatestWithPrevious($latest, $prevLatest);

        return new CurrenciesCollection($latestComparedCurrencies);
    }
}
