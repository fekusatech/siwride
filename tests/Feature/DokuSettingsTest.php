<?php

use App\Models\ApiLog;
use App\Models\DokuSetting;
use App\Models\Setting;
use App\Models\User;
use App\Services\DokuClient;
use App\Services\DokuSignature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

function dokuPayload(): array
{
    return ['enabled' => true, 'environment' => 'sandbox', 'sandbox_client_id' => 'sandbox-client', 'sandbox_secret_key' => 'sandbox-secret', 'production_client_id' => 'production-client', 'production_secret_key' => 'production-secret'];
}

it('restricts both DOKU routes to admins', function () {
    $this->get(route('admin.settings.doku.edit'))->assertRedirect(route('admin.login'));
    $this->put(route('admin.settings.doku.update'), [])->assertRedirect(route('admin.login'));
    $this->actingAs(User::factory()->create(['role' => 'driver']));
    $this->get(route('admin.settings.doku.edit'))->assertForbidden();
    $this->put(route('admin.settings.doku.update'), [])->assertForbidden();
});

it('stores both environments encrypted and exposes only safe props', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->put(route('admin.settings.doku.update'), dokuPayload())->assertSessionHasNoErrors()->assertRedirect(route('admin.settings.doku.edit'));
    $settings = DokuSetting::query()->findOrFail(1);
    expect(Setting::query()->where('setting_key', 'like', '%doku%')->exists())->toBeFalse();
    expect(ApiLog::query()->get()->toJson())->not->toContain('sandbox-secret', 'production-secret');
    foreach (['sandbox', 'production'] as $environment) {
        expect($settings->getAttribute($environment.'_secret_key'))->toBe($environment.'-secret')
            ->and(DB::table('doku_settings')->value($environment.'_secret_key'))->not->toContain($environment.'-secret')
            ->and($settings->toArray())->not->toHaveKey($environment.'_secret_key');
    }
    $response = $this->get(route('admin.settings.doku.edit'));
    $response->assertInertia(fn (Assert $page) => $page->component('Admin/Settings/Doku')->where('doku.sandbox_has_secret', true)->where('doku.production_has_secret', true)->missing('doku.sandbox_secret_key')->missing('doku.production_secret_key'));
    expect($response->getContent())->not->toContain('sandbox-secret', 'production-secret');
    $this->getJson('/api/v1/settings')->assertSuccessful()->assertDontSee('sandbox-secret')->assertDontSee('production-secret');
});

it('requires only selected credentials on activation and never flashes secrets', function (string $environment) {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->from(route('admin.settings.doku.edit'))->put(route('admin.settings.doku.update'), ['enabled' => true, 'environment' => $environment, 'sandbox_secret_key' => 'sensitive'])->assertSessionHasErrors($environment === 'sandbox' ? ['sandbox_client_id'] : ['production_client_id', 'production_secret_key']);
    expect(session()->getOldInput())->not->toHaveKey('sandbox_secret_key')->not->toHaveKey('production_secret_key');
    expect(DokuSetting::query()->count())->toBe(0);
    $this->put(route('admin.settings.doku.update'), ['enabled' => true, 'environment' => $environment, $environment.'_client_id' => 'client', $environment.'_secret_key' => 'secret'])->assertSessionHasNoErrors();
})->with(['sandbox', 'production']);

it('preserves blank secrets replaces new secrets and keeps one record', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->put(route('admin.settings.doku.update'), dokuPayload())->assertSessionHasNoErrors();
    $this->put(route('admin.settings.doku.update'), [...dokuPayload(), 'environment' => 'production', 'sandbox_secret_key' => '', 'production_secret_key' => null])->assertSessionHasNoErrors();
    expect(DokuSetting::find(1)->sandbox_secret_key)->toBe('sandbox-secret')->and(DokuSetting::find(1)->production_secret_key)->toBe('production-secret');
    $this->put(route('admin.settings.doku.update'), [...dokuPayload(), 'production_secret_key' => 'replacement'])->assertSessionHasNoErrors();
    expect(DokuSetting::find(1)->production_secret_key)->toBe('replacement')->and(DokuSetting::count())->toBe(1);
});

it('allows incomplete disabled configuration and never mixes admin credentials with env fallback', function () {
    config(['services.doku.enabled' => true, 'services.doku.client_id' => 'env-client', 'services.doku.secret_key' => 'env-secret']);
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->put(route('admin.settings.doku.update'), ['enabled' => false, 'environment' => 'production'])->assertSessionHasNoErrors();
    Http::fake();
    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', []))->toThrow(LogicException::class);
    DokuSetting::find(1)->update(['enabled' => true]);
    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', []))->toThrow(LogicException::class);
    Http::assertNothingSent();
});

it('uses selected admin credentials and applies changes to the same client immediately', function () {
    Http::preventStrayRequests();
    Http::fake();
    config(['services.doku.enabled' => true, 'services.doku.environment' => 'sandbox', 'services.doku.client_id' => 'env-client', 'services.doku.secret_key' => 'env-secret']);
    $client = app(DokuClient::class);
    $client->post('/checkout/v1/payment', []);
    Http::assertSent(fn ($request) => $request->hasHeader('Client-Id', 'env-client'));
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    foreach (['sandbox', 'production'] as $environment) {
        $this->put(route('admin.settings.doku.update'), [...dokuPayload(), 'environment' => $environment])->assertSessionHasNoErrors();
        $client->post('/checkout/v1/payment', []);
        Http::assertSent(function ($request) use ($environment) {
            if (! $request->hasHeader('Client-Id', $environment.'-client')) {
                return false;
            }
            expect($request->url())->toBe(($environment === 'sandbox' ? 'https://api-sandbox.doku.com' : 'https://api.doku.com').'/checkout/v1/payment')
                ->and($request->header('Signature')[0])->toBe(app(DokuSignature::class)->generate($environment.'-client', $request->header('Request-Id')[0], $request->header('Request-Timestamp')[0], '/checkout/v1/payment', $request->body(), $environment.'-secret'));

            return true;
        });
    }
});

it('rejects invalid environments without logging or flashing submitted secrets', function () {
    Log::spy();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->put(route('admin.settings.doku.update'), [...dokuPayload(), 'environment' => 'invalid'])->assertSessionHasErrors('environment');
    expect(session()->getOldInput())->not->toHaveKey('sandbox_secret_key')->not->toHaveKey('production_secret_key');
    expect(DokuSetting::count())->toBe(0);
    Log::shouldNotHaveReceived('debug');
    Log::shouldNotHaveReceived('info');
    Log::shouldNotHaveReceived('error');
});
