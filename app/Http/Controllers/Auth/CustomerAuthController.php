<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerAuthController extends Controller
{
    /**
     * Show the customer login form.
     */
    public function showLoginForm()
    {
        return Inertia::render('customer/auth/Login');
    }

    /**
     * Handle an incoming authentication request for customers.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if email exists in customers table and has a password
        $customer = Customer::where('email', $credentials['email'])->first();
        if (!$customer || is_null($customer->password)) {
            return redirect()->route('customer.register', ['email' => $credentials['email']])
                ->with('error', 'This email address is not registered. Please create an account first.');
        }

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('customer.profile'));
        }

        throw ValidationException::withMessages([
            'email' => trans('auth.failed'),
        ]);
    }

    /**
     * Show the customer registration form.
     */
    public function showRegisterForm()
    {
        return Inertia::render('customer/auth/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('customers')->whereNotNull('password'),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|confirmed|min:8',
        ], [
            'email.unique' => 'This email address is already registered. Please log in instead.',
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if ($customer) {
            $customer->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
        } else {
            $customer = Customer::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
            ]);
        }

        Auth::guard('customer')->login($customer);

        return redirect()->route('customer.profile')->with('success', 'Welcome! Your account has been created successfully.');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
