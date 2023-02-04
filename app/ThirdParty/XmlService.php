<?php

namespace App\ThirdParty;

use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleXMLElement;

class XmlService
{
    /**
     * Распарсить xml в массив
     *
     * @param string $xml
     * @param bool $isUrl Является $xml url, по которому лежит xml
     * @return array
     *
     * Возвращает массив вида:
     * [
     *  date => ...,
     *  name => ...,
     *  currencies => [
     *                  EUR => [
     *                          id => ...,
     *                          num_code => ...,
     *                          ... ]
     *                          ],
     *                  USD => [...],
     *                  ...,
     * ]
     * @throws Exception
     */
    public function parseXmlToArray(
        string $xml,
        bool   $isUrl
    ): array
    {
        $xmlObject = new SimpleXMLElement($xml, dataIsURL: $isUrl);

        return $this->createArrayFrom($xmlObject);
    }

    private function createArrayFrom(SimpleXMLElement $xmlObject): array
    {
        $parsed = [];

        foreach ($xmlObject->attributes() as $name => $attribute) {
            $parsed[strtolower($name)] = (string)$attribute;
        }

        foreach ($xmlObject->Valute as $valute) {
            $charCode = (string)$valute->CharCode;

            foreach ($valute->attributes() as $name => $attribute) {
                $parsed['currencies'][$charCode][strtolower($name)] = (string)$attribute;
            }

            foreach ($valute->children() as $key => $child) {
                $parsed['currencies'][$charCode][Str::snake($key)] = (string)$child;
            }
        }

        return $parsed;
    }

    /**
     * @throws FileNotFoundException
     */
    public function putToFile(string $filename, string $xml): void
    {
        if (!Storage::put($filename, $xml)) {
            throw new FileNotFoundException(
                "Couldn't put xml to file" . config('currencies.xml_filename')
            );
        }
    }
}
