<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Setting;
use App\Services\RecaptchaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

it('shares only explicitly public settings', function () {
    Setting::setValue('business_name', 'Public business');
    foreach (['xendit_secret_key', 'xendit_webhook_token', 'recaptcha_secret_key', 'future_secret'] as $key) {
        Setting::setValue($key, 'must-not-leak');
    }
    $request = Request::create('/');
    $request->setLaravelSession(app('session.store'));
    $settings = app(HandleInertiaRequests::class)->share($request)['settings'];
    expect($settings['business_name'])->toBe('Public business')
        ->and(json_encode($settings))->not->toContain('must-not-leak');
});

it('fails closed when the webhook token is missing even when a database token exists', function () {
    config(['services.xendit.webhook_token' => null]);
    Setting::setValue('xendit_webhook_token', 'legacy-token');
    Log::spy();
    $this->postJson(route('webhooks.xendit.invoice'), [], ['x-callback-token' => 'legacy-token'])->assertStatus(503);
    Log::shouldHaveReceived('error')->with('Xendit webhook token is not configured')->once();
});

it('rejects invalid webhook tokens and accepts only the configured token', function () {
    config(['services.xendit.webhook_token' => 'configured-token']);
    $this->postJson(route('webhooks.xendit.invoice'), [], ['x-callback-token' => 'wrong'])->assertForbidden();
    $this->postJson(route('webhooks.xendit.invoice'), [], ['x-callback-token' => 'configured-token'])->assertSuccessful();
});

it('reads recaptcha secrets exclusively from configuration', function () {
    Setting::setValue('recaptcha_secret_key', 'legacy-secret');
    config(['services.recaptcha.secret_key' => 'configured-secret']);
    expect(app(RecaptchaService::class)->secretKey())->toBe('configured-secret');
});
