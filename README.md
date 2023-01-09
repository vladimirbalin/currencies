# Exchange rates application
Приложение получающее значения курсов для заданного списка валют, проверяющее вырос или упал ли курс по сравнению
с предыдущим значением курса. Виджет отображающий эти данные.

## Необходимо реализовать:

Необходимо разработать следующую функциональность:

1.	Скрипт получения ЦБ-курсов валют:
-	скрипт получает значение курсов с http://www.cbr.ru/scripts/XML_daily.asp для заданного списка валют;
-	для каждой даты и валюты записывает полученные значения в базу данных;
-	предполагается, что скрипт запускается с определенной периодичностью и добавляет/обновляет значение курсов валют на каждый день.

2.	Виджет, выводящий ЦБ-курсы валют на текущий день:
-	виджет показывает ЦБ -курсы валют;
-	для каждой валюты должно быть ее текущее значение и изменение по отношению к предыдущему дню (показывать либо стрелкой «вверх», «вниз» либо выделять цветом – «красный» - курс упал, «зеленый» - курс вырос);
-	так как значение курсов валют может изменяться в течение дня, сделать автоматическое обновление этих значений в виджете с заданным интервалом.

3.	Должны быть следующие настройки:
-	список валют, курсы которых необходимо получать с ЦБ;
-	список валют, которые необходимо выводить в виджете;
-	интервал обновления содержимого виджета.


## Реализовано
1. Бэкэнд на Laravel, который посылает запросы на единственный endpoint цб, полученные данные(xml) записывает в xml файл,
   парсит его и записывает необходимые данные в базу данных.

- Репозиторий: [CurrencyRepository](./app/Repositories/CurrencyRepository.php) - только для получения данных.
- Сервисный слой: [CurrencyService](./app/Services/CurrencyService.php) - для манипулирования данными, бизнес логика.
- Контроллер: [MainController](./app/Http/Controllers/MainController.php)
- Миграции: [database/migrations](./database/migrations)
- Ресурсы: [app/Http/Resources](./app/Http/Resources) - возвращаемые ресурсы фронту
- Планировщик: [app/Console/Kernel.php](./app/Console/Kernel.php) - запуск заданий(Jobs) на обновление данных, полученных с ЦБ через определенный промежуток времени
- Jobs: [app/Jobs](./app/Jobs) - задания на обновление данных
- Файл конфигурации: [config/currencies](./config/currencies.php)

2. Фронтэнд - виджет на нативном js, который посылает запросы к нашему бэкэнду, полученные данные выводит в нужном виде.

- Директория [widget](./widget)

## Конфигурирование:
**Бэкэнд**:
В [config/currencies.php](./config/currencies.php) находится массив с конфигурируемыми параметрами:
- `xml_filename` - название xml файла, в который будем помещать данные, полученные от цб;
- `currency_codes` - массив кодов валют, которые будем брать с xml файла и помещать в базу данных. Например: `['EUR', 'USD', 'CAD', 'BYN', ...]` или `['*']`;
- `cbr_endpoint` - эндпоинт цб, взят из задания.

**Фронтэнд**:
  В [widget/js/config.js](./widget/js/config.js) находится массив с конфигурируемыми параметрами:
- `currencies` - массив кодов валют, которые будем получить с бэкэнда;
- `backendEndpoint` - эндпоинт бэкэнда, который будет получать массив необходимых виджету валют(из предыдущего параметра), и отдавать их;
- `refreshTimer` - интервал обновления содержимого виджета, в секундах.

## Установка

1. Клонируем репозиторий, устанавливаем зависимости с помощью composer

```bash
git clone https://github.com/vladimirbalin/currencies
cd currencies
composer install
```

2. Создаём базу данных, устанавливаем соответсвующие вашей базе параметры в .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=currencies
DB_USERNAME=
DB_PASSWORD=
```
3. Проводим миграции:
 ```
php artisan migrate
 ```
4. Запуск планировщика:
 ```
 php artisan schedule:work
 ```
5. Запуск обработчика очередей:
 ```
 php artisan queue:work
 ```

### API бэка, к которому обращается наш виджет

#### Получить рейты необходимых валют

```http
  GET /api/currencies
```

| Parameter | Type    | Description                |
| :-------- |:--------| :------------------------- |
| `currencies` | `array` | **Required**. Коды валют |

Например: `api/currencies?currencies[]=EUR&currencies[]=USD&currencies[]=BYN&currencies[]=CAD`

Возвращает JSON вида:
```javascript
{
    "currencies": [
        {
            "char_code":"BYN",
            "value":"25.7044",
            "date":"2022-12-31",
            "nominal":1,
            "status":"rateDown"
        },
        ...
    ]
}
```

