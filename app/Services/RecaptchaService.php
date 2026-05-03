<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RecaptchaService
{
    public function enabled(): bool
    {
        return Setting::getValue('recaptcha_enabled', '0') === '1'
            && filled($this->siteKey())
            && filled($this->secretKey());
    }

    public function siteKey(): ?string
    {
        return Setting::getValue('recaptcha_site_key');
    }

    public function secretKey(): ?string
    {
        return Setting::getValue('recaptcha_secret_key');
    }

    public function validate(Request $request): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ($request->attributes->get('recaptcha_validated') === true) {
            return;
        }

        $token = $request->string('g-recaptcha-response')->toString();

        Log::info('reCAPTCHA token received.', [
            'length' => strlen($token),
            'prefix' => substr($token, 0, 12),
        ]);

        if ($token === '') {
            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'Please complete the reCAPTCHA verification.',
            ]);
        }

        $response = Http::asForm()
            ->timeout(5)
            ->connectTimeout(3)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secretKey(),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

        if (! $response->json('success', false)) {
            $errorCodes = $response->json('error-codes', []);
            Log::warning('reCAPTCHA verification failed.', [
                'error_codes' => $errorCodes,
                'hostname' => $response->json('hostname'),
            ]);

            throw ValidationException::withMessages([
                'g-recaptcha-response' => 'reCAPTCHA verification failed: '.implode(', ', $errorCodes),
            ]);
        }

        $request->attributes->set('recaptcha_validated', true);
    }
}
