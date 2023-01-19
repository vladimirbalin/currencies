<?php

namespace App\Jobs;

use App\Services\CurrenciesDownloadService;
use App\Services\CurrencyService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchRatesInXmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param CurrencyService $currencyService
     * @param CurrenciesDownloadService $downloadService
     * @return void
     * @throws GuzzleException
     * @throws FileNotFoundException
     */
    public function handle(
        CurrencyService           $currencyService,
        CurrenciesDownloadService $downloadService): void
    {
        $xml = $downloadService->fetch();
        $currencyService->putToFile(
            config('currencies.xml_filename'),
            $xml
        );
    }
}
