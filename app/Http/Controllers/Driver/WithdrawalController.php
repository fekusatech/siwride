<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Services\DriverWithdrawalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WithdrawalController extends Controller
{
    public function store(Request $request, DriverWithdrawalService $withdrawalService): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $withdrawal = $withdrawalService->request(
                Auth::guard('driver')->user(),
                $validated['amount'],
                $validated['bank_name'],
                $validated['bank_account_number'],
                $validated['bank_account_name'],
            );
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())
                ->withInput();
        }

        return back()->with('success', 'Withdrawal request of Rp '.number_format((float) $withdrawal->amount, 0, ',', '.').' submitted for approval.');
    }
}
