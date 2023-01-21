<?php

namespace App\Services;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
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
     * @throws GuzzleException|LogicException
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
                $this->repository->getCurrency(
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
     * @param Collection $latest Коллекция курсов валют, за последнюю дату обновления
     * @param Collection $prevLatest Коллекция курсов валют, за предпоследнюю дату обновления
     * @return Collection Коллекция с полем 'status' - в котором указано,
     * увеличился или упал курс относительно предыдущего
     */
    public function comparedLatestWithPrevious(
        Collection $latest,
        Collection $prevLatest
    ): Collection
    {
        $latest = $latest->map(function ($currency) use ($prevLatest) {
            $currentCharCode = $currency['char_code'];
            $currency['status'] = $this->assignStatus(
                $currency,
                $prevLatest
                    ->where('char_code', '=', $currentCharCode)
                    ->first()
            );
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
     * @param Model $latest
     * @param Model $prevLatest
     * @return string
     */
    private function assignStatus(Model $latest, Model $prevLatest): string
    {
        if (! isset($latest) || ! isset($prevLatest)) {
            throw new InvalidArgumentException('Passed invalid currency parameters');
        }

        if ($latest['value'] < $prevLatest['value']) {
            return 'rateDown';
        } elseif ($latest['value'] > $prevLatest['value']) {
            return 'rateUp';
        }

        return 'same';
    }


}
