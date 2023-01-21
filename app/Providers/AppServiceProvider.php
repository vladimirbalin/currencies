<?php

namespace App\Providers;

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
    public function register()
    {
        $this->app->bind(Reader::class, function ($app){
            return new Reader($app->make(Document::class));
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
