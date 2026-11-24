<?php

namespace App\Http\Requests;

use App\Services\DisplayCurrency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<mixed>> */
    public function rules(): array
    {
        return ['currency' => ['required', Rule::in(DisplayCurrency::CURRENCIES)]];
    }
}
