<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCurrencyRequest;
use App\Services\DisplayCurrency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class CurrencyController extends Controller
{
    public function update(UpdateCurrencyRequest $request, DisplayCurrency $currency): RedirectResponse
    {
        $selected = $request->validated('currency');
        if ($currency->context($selected)['selected'] !== $selected) {
            throw ValidationException::withMessages(['currency' => 'Exchange rates are unavailable. Prices and payments remain in IDR.']);
        }
        $request->session()->put('display_currency', $selected);

        return back();
    }
}
