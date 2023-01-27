<?php

namespace App\Providers;

use App\Services\Currency\CurrencyService;
use App\ThirdParty\CBR;
use Illuminate\Support\ServiceProvider;
use Orchestra\Parser\Xml\Document;
use Orchestra\Parser\Xml\Reader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //парсер xml
        $this->app->bind(Reader::class, function ($app){
            return new Reader($app->make(Document::class));
        });

        // передаем нужный источник xml, например из ответа API либо файл
        $this->app->when(CurrencyService::class)
            ->needs('$xmlSource')
            ->give(function ($app) {
                return $app->make(CBR::class)->fetchCurrencies();
            });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
