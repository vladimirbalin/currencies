<?php

namespace App\Jobs;

use App\Services\CurrencyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
    )
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param CurrencyService $currencyService
     * @return void
     * @throws GuzzleException
     */
    public function handle(CurrencyService $currencyService): void
    {
        $currencyService->updateOrCreateExchangeRatesInDb();
    }
}
