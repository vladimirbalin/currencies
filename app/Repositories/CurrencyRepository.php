<?php

namespace App\Repositories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class CurrencyRepository extends Repository
{
    protected function getModelClass(): string
    {
        return Currency::class;
    }

    /**
     * Получить коллекцию указанных курсов валют, за последнюю дату обновления
     *
     * @param array $currenciesCharCodes
     * @return Collection
     */
    public function getAllLatest(
        array $currenciesCharCodes
    ): Collection
    {
        if(! isset($currenciesCharCodes)){
            throw new InvalidArgumentException('Currencies char codes array wasn\'t provided');
        }

        return $this
            ->model()
            ->newQuery()
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
     * @return Collection
     */
    public function getAllPrevLatest(
        array $currenciesCharCodes
    ): Collection
    {
        if(! isset($currenciesCharCodes)){
            throw new InvalidArgumentException('Currencies char codes array wasn\'t provided');
        }

        return $this
            ->model()
            ->newQuery()
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
        return $this
            ->model()
            ->newQuery()
            ->max('date');
    }

    /**
     * Получить предпоследнюю дату обновления курса валют
     *
     * @return mixed
     */
    public function prevLatestDate(): mixed
    {
        return $this
            ->model()
            ->newQuery()
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
            ->model()
            ->newQuery()
            ->where([
                ['char_code', '=', $currencyCharCode],
                ['date', '=', $date]
            ])->first();
    }

}
