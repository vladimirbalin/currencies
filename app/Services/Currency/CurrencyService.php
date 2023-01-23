<?php

namespace App\Services\Currency;

use App\Models\Currency;
use App\Repositories\CurrencyRepository;
use App\ThirdParty\XmlService;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;

class CurrencyService
{
    private array $configCurrencyCodes;

    public function __construct(
        private CurrencyRepository     $repository,
        private XmlService             $xmlService,
        private string                 $xmlSource,
    )
    {
        $this->configCurrencyCodes = config('currencies.currency_codes');
    }

    /**
     * Обновить или создать новые записи курсов валют в базе данных
     *
     * @return void
     * @throws LogicException
     */
    public function updateOrCreateExchangeRatesInDb(): void
    {
        if ($this->currenciesConfigIsEmpty()) {
            return;
        }

        $currencies = $this
            ->xmlService
            ->parseXmlToArray($this->xmlSource);

        $currencies['date'] = $this->convertDateToDateString($currencies['date']);

        //идём по каждой валюте из массива, полученного из xml ресурса от цб
        foreach ($currencies['currencies'] as $currency) {

            //если в конфиге не задана валюта, пропускаем её
            if (! $this->shouldGetCurrency($currency['charCode'])) {
                continue;
            }

            //пробуем забрать из базы данных запись конкретной валюты, за дату из xml файла
            $lastUpdateCurrency =
                $this->repository->getCurrency(
                    $currency['charCode'],
                    $currencies['date']
                );

            if (! $lastUpdateCurrency) {
                $lastUpdateCurrency = new Currency();
            }

            //присваиваем или обновляем значения модели
            $this->assignOrUpdateProperties(
                $lastUpdateCurrency,
                $currency,
                $currencies['date']
            );

            if (! $lastUpdateCurrency->save()) {
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

    private function assignOrUpdateProperties(
        Currency $todayRateCurrency,
        array    $currency,
        string   $date
    ): void
    {
        $todayRateCurrency->valute_id = $currency['valuteId'];
        $todayRateCurrency->num_code = $currency['numCode'];
        $todayRateCurrency->char_code = $currency['charCode'];
        $todayRateCurrency->nominal = $currency['nominal'];
        $todayRateCurrency->name = $currency['name'];
        $todayRateCurrency->value = $this->convertValueStringToFloat($currency['value']);
        $todayRateCurrency->date = $date;
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
     * @param array $charCodes
     * @return Collection<Currency> Коллекция валют с добавленным полем 'status' в каждой,
     * в котором указано, увеличился или упал курс относительно предыдущего обновления
     */
    public function getLatestWithStatus(
        array $charCodes
    ): Collection
    {
        $latest = $this->repository->getAllLatest($charCodes);
        $prevLatest = $this->repository->getAllPrevLatest($charCodes);

        return $latest->map($this->callback($prevLatest));
    }

    private function callback($prevLatest): Closure
    {
        return function ($latest) use ($prevLatest) {
            $currentCharCode = $latest['char_code'];

            $latest['status'] = $this->assignStatus(
                $latest,
                $prevLatest
                    ->where('char_code', '=', $currentCharCode)
                    ->first()
            );
            return $latest;
        };
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
        if ($latest['value'] < $prevLatest['value']) {
            return 'rateDown';
        } elseif ($latest['value'] > $prevLatest['value']) {
            return 'rateUp';
        }

        return 'same';
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

        return in_array(
            $charCode,
            $this->configCurrencyCodes
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
        return count($this->configCurrencyCodes) == 1 &&
            in_array(
                '*',
                $this->configCurrencyCodes
            );
    }

    private function currenciesConfigIsEmpty(): bool
    {
        if (empty($this->configCurrencyCodes)) {
            return true;
        }

        return false;
    }
}
