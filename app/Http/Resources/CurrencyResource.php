<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'char_code' => $this->char_code,
            'value' => $this->value,
            'date' => $this->date,
            'nominal' => $this->nominal,
            'status' => $this->status,
        ];
    }
}
