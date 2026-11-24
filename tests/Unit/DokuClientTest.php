<?php

use App\Services\DokuClient;
use App\Services\DokuSignature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
    config([
        'services.doku.enabled' => true,
        'services.doku.environment' => 'sandbox',
        'services.doku.client_id' => 'test-client',
        'services.doku.secret_key' => 'test-secret',
        'services.doku.timeout' => 15,
        'services.doku.connect_timeout' => 5,
    ]);
});

it('sends signed JSON to the configured DOKU environment', function (string $environment, string $host) {
    config(['services.doku.environment' => $environment]);
    $this->freezeTime();
    Http::fake([$host.'/*' => Http::response(['status' => 'ok'])]);

    $payload = ['order' => ['invoice_number' => 'TEST/001', 'amount' => 10000], 'description' => 'Perjalanan Jakarta'];
    $response = app(DokuClient::class)->post('/checkout/v1/payment', $payload);

    expect($response->json('status'))->toBe('ok');
    Http::assertSent(function (Request $request) use ($host, $payload): bool {
        $requestId = $request->header('Request-Id')[0];
        $timestamp = $request->header('Request-Timestamp')[0];

        expect($request->url())->toBe($host.'/checkout/v1/payment')
            ->and($request->method())->toBe('POST')
            ->and($request->body())->toBe(json_encode($payload, JSON_THROW_ON_ERROR))
            ->and($request->header('Client-Id'))->toBe(['test-client'])
            ->and($request->header('Content-Type'))->toBe(['application/json'])
            ->and($requestId)->toMatch('/^[0-9a-f-]{36}$/')
            ->and($timestamp)->toBe(now()->utc()->format('Y-m-d\TH:i:s\Z'))
            ->and($request->header('Signature'))->toBe([
                app(DokuSignature::class)->generate('test-client', $requestId, $timestamp, '/checkout/v1/payment', $request->body(), 'test-secret'),
            ]);

        return true;
    });
    Http::assertSentCount(1);
})->with([
    ['sandbox', 'https://api-sandbox.doku.com'],
    ['production', 'https://api.doku.com'],
]);

it('rejects invalid configuration before sending a request', function (string $key, mixed $value) {
    config(['services.doku.'.$key => $value]);
    Http::fake();

    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', []))->toThrow(LogicException::class);
    Http::assertNothingSent();
})->with([
    ['enabled', false],
    ['environment', 'invalid'],
    ['client_id', null],
    ['secret_key', '  '],
    ['timeout', 0],
    ['connect_timeout', -1],
]);

it('rejects external URLs and unsupported paths', function (string $path) {
    Http::fake();

    expect(fn () => app(DokuClient::class)->post($path, []))->toThrow(InvalidArgumentException::class);
    Http::assertNothingSent();
})->with(['https://example.com/payment', '//example.com', 'checkout/v1/payment', '/checkout?test=1', "/checkout\nheader", '/checkout/../payment']);

it('propagates HTTP errors without retrying', function (int $status) {
    Http::fake(['*' => Http::response(['error' => 'rejected'], $status)]);

    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', []))->toThrow(RequestException::class);
    Http::assertSentCount(1);
})->with([400, 401, 429, 500]);

it('propagates connection failures without retrying', function () {
    $attempts = 0;
    Http::fake(function () use (&$attempts) {
        $attempts++;

        throw new ConnectionException('Connection timed out.');
    });

    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', []))->toThrow(ConnectionException::class)
        ->and($attempts)->toBe(1);
});

it('rejects invalid JSON before sending a request', function () {
    Http::fake();

    expect(fn () => app(DokuClient::class)->post('/checkout/v1/payment', ['amount' => NAN]))->toThrow(JsonException::class);
    Http::assertNothingSent();
});
