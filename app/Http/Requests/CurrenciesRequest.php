<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CurrenciesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'currencies' => 'required|array',
            'currencies.*' => 'required|string|min:3|max:3'
        ];
    }
}
