<?php

namespace App\ThirdParty;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;

class XmlService
{
    /**
     * Распарсить xml в массив
     *
     * @param string $xml
     * @return array
     */
    public function parseXmlToArray(string $xml): array
    {
        $json = json_encode($xml);
        $arr = json_decode($json,TRUE);

        return $arr;
    }

    /**
     * @throws FileNotFoundException
     */
    public function putToFile(string $filename, string $xml): void
    {
        if (! Storage::put($filename, $xml)) {
            throw new FileNotFoundException(
                "Couldn't put xml to file" . config('currencies.xml_filename')
            );
        }
    }
}
