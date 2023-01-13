<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CurrenciesCollection extends ResourceCollection
{
    public $collects = CurrencyResource::class;
    public static $wrap = 'currencies';
}
