<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
    public function login(Request $request, RecaptchaService $recaptchaService)
    {
        $recaptchaService->validate($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if email exists in customers table and has a password
        $customer = Customer::where('email', $credentials['email'])->first();
        if (! $customer || is_null($customer->password)) {
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
     * Show the forgot password form.
     */
    public function showForgotPasswordForm()
    {
        return Inertia::render('customer/auth/ForgotPassword');
    }

    /**
     * Send a password reset link to the customer.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = Customer::where('email', $request->email)->first();

        if (! $customer || is_null($customer->password)) {
            return inertia()->location('/customer/forgot-password?status=not_found');
        }

        $token = Str::random(64);

        // Store the token in the password_reset_tokens table
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Send the reset link email
        $emailSent = false;
        try {
            \Mail::raw("Click the link below to reset your password:\n\n".url('/customer/reset-password?token='.$token.'&email='.urlencode($request->email))."\n\nThis link will expire in 60 minutes.\n\nIf you did not request a password reset, please ignore this email.", function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Password Reset Request - SIWRide');
            });
            $emailSent = true;
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: '.$e->getMessage());
        }

        if ($emailSent) {
            return inertia()->location('/customer/forgot-password?status=sent');
        }

        return inertia()->location('/customer/forgot-password?status=failed');
    }

    /**
     * Show the reset password form.
     */
    public function showResetPasswordForm(Request $request)
    {
        $token = $request->query('token');
        $email = $request->query('email');

        if (! $token || ! $email) {
            return redirect()->route('customer.login');
        }

        return Inertia::render('customer/auth/ResetPassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Reset the customer's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $resetRecord || ! Hash::check($request->token, $resetRecord->token)) {
            throw ValidationException::withMessages([
                'email' => ['The password reset token is invalid or has expired.'],
            ]);
        }

        // Check if token is expired (60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            throw ValidationException::withMessages([
                'email' => ['The password reset token has expired. Please request a new one.'],
            ]);
        }

        // Update the password
        $customer = Customer::where('email', $request->email)->first();

        if ($customer) {
            $customer->update([
                'password' => Hash::make($request->password),
            ]);
        }

        // Delete the reset token
        \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        return redirect()->route('customer.login')->with('status', 'Your password has been reset successfully. You can now log in.');
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
