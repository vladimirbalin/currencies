<?php

namespace App\Jobs;

use App\Services\Currency\CurrencyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRatesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Seconds between tries
     *
     * @var int
     */
    public int $backoff = 5;

    /**
     * Execute the job.
     *
     * @param CurrencyService $currencyService
     * @return void
     */
    public function handle(CurrencyService $currencyService): void
    {
        $currencyService->updateOrCreateExchangeRatesInDb();
    }
}
