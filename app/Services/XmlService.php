<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Orchestra\Parser\Xml\Reader;

class XmlService
{
    public function __construct(
        private Reader $xmlReader
    )
    {
    }

    /**
     * Распарсить xml в массив
     *
     * @param string $xml
     * @return array
     */
    public function parseXmlToArray(string $xml): array
    {
        $schema = [
            'date' => ['uses' => '::Date'],
            'name' => ['uses' => '::name'],
            'currencies' => [
                'uses' => 'Valute[::ID>valuteId,NumCode>numCode,CharCode>charCode,Name>name,Value>value,Nominal>nominal]'
            ]
        ];

        $arr = $this
            ->xmlReader
            ->extract($xml)
            ->parse($schema);

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
