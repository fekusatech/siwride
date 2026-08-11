<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DriverAuthController extends Controller
{
    /**
     * Show the driver login form.
     */
    public function showLoginForm()
    {
        return Inertia::render('Driver/auth/Login');
    }

    /**
     * Handle an incoming authentication request for drivers.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('driver')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::guard('driver')->user();

        if (! $user->isDriver() || $user->status !== 'active') {
            Auth::guard('driver')->logout();

            throw ValidationException::withMessages([
                'email' => $user->isDriver()
                    ? 'Your driver account is still awaiting admin approval.'
                    : 'This account is not registered as a driver.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('driver.dashboard'));
    }

    /**
     * Destroy an authenticated driver session.
     */
    public function logout(Request $request)
    {
        Auth::guard('driver')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('driver.login');
    }
}
