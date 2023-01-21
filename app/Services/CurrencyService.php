<?php

namespace App\Services;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;
use InvalidArgumentException;

class CurrencyService
{
    public function __construct(
        private CurrencyRepository     $repository,
        private CurrenciesFetchService $fetchService,
        private XmlService             $xmlService
    )
    {
    }

    /**
     * Обновить или создать новые записи курсов валют в базе данных
     *
     * @return void
     * @throws GuzzleException
     */
    public function updateOrCreateExchangeRatesInDb(): void
    {
        if ($this->currenciesConfigIsEmpty()) {
            return;
        }

        $xml = $this->fetchService->fetch();
        $currencies = $this->xmlService->parseXmlToArray($xml);

        $currencies['date'] = $this->convertDateToDateString($currencies['date']);

        //идём по каждой валюте из массива, полученного из xml ресурса от цб
        foreach ($currencies['currencies'] as $currency) {

            //если в конфиге не задана валюта, пропускаем её
            if (
                ! $this->shouldGetCurrency($currency['charCode'])
            ) {
                continue;
            }

            //пробуем забрать из базы данных запись конкретной валюты, за дату из xml файла
            $todayRateCurrency =
                $this
                    ->currencyRepository
                    ->getCurrency(
                        $currency['charCode'],
                        $currencies['date']
                    );

            //если её нет в базе, значит это новые данные - создаём модель
            if (! $todayRateCurrency) {
                $todayRateCurrency = new Currency();
            }

            //присваиваем или обновляем значения модели
            $todayRateCurrency->valute_id = $currency['valuteId'];
            $todayRateCurrency->num_code = $currency['numCode'];
            $todayRateCurrency->char_code = $currency['charCode'];
            $todayRateCurrency->nominal = $currency['nominal'];
            $todayRateCurrency->name = $currency['name'];
            $todayRateCurrency->value = $this->convertValueStringToFloat($currency['value']);
            $todayRateCurrency->date = $currencies['date'];

            //записываем в базу данных
            if (! $todayRateCurrency->save()) {
                throw new LogicException('Cannot save current currency to db');
            }
        }
    }

    private function convertDateToDateString(string $date): string
    {
        return Carbon::createFromFormat(
            'd.m.Y', $date
        )->toDateString();
    }

    /**
     * Конвертировать строку значения курса валют во float
     *
     * @param string $value
     * @return float
     */
    private function convertValueStringToFloat(string $value): float
    {
        return (float)str_replace(',', '.', $value);
    }

    /**
     * Получить коллекцию указанного списка валют, с полем status,
     * значение которого указывает на изменение value к предыдущему обновлению
     *
     * @param array $currenciesCharCodes
     * @return Collection
     * @throws InvalidArgumentException
     */
    public function getCurrencies(
        array $currenciesCharCodes
    ): Collection
    {
        if (! isset($currenciesCharCodes)) {
            throw new InvalidArgumentException('Currencies char codes are not provided');
        }

        $latest = $this
            ->currencyRepository
            ->getAllLatest($currenciesCharCodes);
        $prevLatest = $this
            ->currencyRepository
            ->getAllPrevLatest($currenciesCharCodes);

        //получаем вид ["USD" => ["char_code" => "USD","value" => "69.9346","date" => "2022-12-28"],
        // "EUR" => ...]
        $prevLatest = $prevLatest->keyBy('char_code');

        $latest = $latest->map(function ($currency) use ($prevLatest) {
            $currency['status'] = $this->assignStatus($currency, $prevLatest);
            return $currency;
        });

        return $latest;
    }

    /**
     * Проверить на наличие кода валюты в поле кодов валют в конфиге,
     * что значит, что мы должны сохранять указанную валюту в базе данных
     *
     * @param string $charCode
     * @return bool
     */
    private function shouldGetCurrency(string $charCode): bool
    {
        if ($this->shouldGetAllCurrencies()) {
            return true;
        }

        $configCurrencyCodes = config('currencies.currency_codes');

        return in_array(
            $charCode,
            $configCurrencyCodes
        );
    }

    /**
     * Проверить на наличие '*' в поле кодов валют в конфиге,
     * что значит, что мы должны сохранять все валюты, которые
     * получаем от цб
     *
     * @return bool
     */
    private function shouldGetAllCurrencies(): bool
    {
        $configCurrencyCodes = config('currencies.currency_codes');

        return count($configCurrencyCodes) == 1 &&
            in_array(
                '*',
                $configCurrencyCodes
            );
    }

    private function currenciesConfigIsEmpty(): bool
    {
        $configCurrencyCodes = config('currencies.currency_codes');

        if (empty($configCurrencyCodes)) {
            return true;
        }

        return false;
    }

    /**
     * Узнать, повысился ли рейт валюты к её предыдущему значению
     *
     * @param array $currency
     * @param string $prevLatest
     * @return string
     * @throws InvalidArgumentException
     */
    private function assignStatus(array $currency, string $prevLatest): string
    {
        if (!isset($currency) || !isset($prevLatest)) {
            throw new InvalidArgumentException('Passed invalid currency parameters');
        }

        $charCode = $currency['char_code'];

        if ($currency['value'] < $prevLatest[$charCode]['value']) {
            return 'rateDown';
        } elseif ($currency['value'] > $prevLatest[$charCode]['value']) {
            return 'rateUp';
        }

        return 'same';
    }


}
