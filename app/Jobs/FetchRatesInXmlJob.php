<?php

namespace App\Jobs;

use App\Services\CurrenciesFetchService;
use App\Services\XmlService;
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
     * @param CurrenciesFetchService $fetchService
     * @param XmlService $xmlService
     * @return void
     * @throws FileNotFoundException
     * @throws GuzzleException
     */
    public function handle(
        CurrenciesFetchService $fetchService,
        XmlService             $xmlService): void
    {
        $xml = $fetchService->fetch();
        $xmlService->putToFile(
            config('currencies.xml_filename'),
            $xml
        );
    }
}
