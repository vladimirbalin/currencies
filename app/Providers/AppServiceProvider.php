<?php

namespace App\Providers;

use App\Services\Currency\CurrencyService;
use App\ThirdParty\CBR;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
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
