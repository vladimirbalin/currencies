<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


class CurrencyRepository extends Repository
{
    public function __construct()
    {
        parent::__construct();

    }

    protected function getModelClass(): string
    {
        return Currency::class;
    }

    /**
     * Получить коллекцию указанных курсов валют, за последнюю дату обновления
     *
     * @param array $currenciesCharCodes
     * @return Collection|null
     */
    public function getAllLatest(
        array $currenciesCharCodes = ['USD', 'EUR']
    ): Collection|null
    {
        return $this
            ->start()
            ->whereIn('char_code', $currenciesCharCodes)
            ->where('date', '=', function ($query) {
                $query
                    ->selectRaw('max(date)')
                    ->from('currencies');
            })->get();
    }

    /**
     * Получить коллекцию указанных курсов валют, за предпоследнюю дату обновления
     *
     * @param array $currenciesCharCodes
     * @return Collection|null
     */
    public function getAllPrevLatest(
        array $currenciesCharCodes = ['USD', 'EUR']
    ): Collection|null
    {
        return $this
            ->start()
            ->whereIn('char_code', $currenciesCharCodes)
            ->where('date', '=', $this->prevLatestDate())
            ->get();
    }

    /**
     * Получить последнюю дату обновления курса валют
     *
     * @return mixed
     */
    public function latestDate(): mixed
    {
        return $this->start()->max('date');
    }

    /**
     * Получить предпоследнюю дату обновления курса валют
     *
     * @return mixed
     */
    public function prevLatestDate(): mixed
    {
        return $this
            ->start()
            ->where('date', '<', function ($query) {
                $query
                    ->selectRaw('max(date)')
                    ->from('currencies');
            })->max('date');
    }

    /**
     * Получить курс указанной валюты за указанный день
     *
     * @param string $currencyCharCode
     * @param string $date
     * @return Model|null
     */
    public function getCurrency(
        string $currencyCharCode,
        string $date
    ): Model|null
    {
        return $this
            ->start()
            ->where([
                ['char_code', '=', $currencyCharCode],
                ['date', '=', $date]
            ])->first();
    }

}
