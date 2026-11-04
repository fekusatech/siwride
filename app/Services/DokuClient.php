<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class DokuClient
{
    public function __construct(private DokuSignature $signature) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload): Response
    {
        if (config('services.doku.enabled') !== true) {
            throw new LogicException('DOKU module is disabled.');
        }

        $baseUrl = match (config('services.doku.environment')) {
            'sandbox' => 'https://api-sandbox.doku.com',
            'production' => 'https://api.doku.com',
            default => throw new LogicException('Invalid DOKU environment.'),
        };

        $clientId = config('services.doku.client_id');
        $secretKey = config('services.doku.secret_key');

        if (! is_string($clientId) || trim($clientId) === '' || ! is_string($secretKey) || trim($secretKey) === '') {
            throw new LogicException('DOKU credentials are not configured.');
        }

        if (! preg_match('~^/[a-zA-Z0-9/_-]+$~D', $path)) {
            throw new InvalidArgumentException('DOKU request target must be an absolute API path without a query string.');
        }

        $timeout = config('services.doku.timeout');
        $connectTimeout = config('services.doku.connect_timeout');

        if (! is_int($timeout) || $timeout < 1 || ! is_int($connectTimeout) || $connectTimeout < 1) {
            throw new LogicException('DOKU timeouts must be positive integers.');
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $requestId = (string) Str::uuid();
        $timestamp = now()->utc()->format('Y-m-d\TH:i:s\Z');

        return Http::acceptJson()
            ->withHeaders([
                'Client-Id' => $clientId,
                'Request-Id' => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature' => $this->signature->generate($clientId, $requestId, $timestamp, $path, $body, $secretKey),
            ])
            ->timeout($timeout)
            ->connectTimeout($connectTimeout)
            ->withoutRedirecting()
            ->withBody($body, 'application/json')
            ->post($baseUrl.$path)
            ->throw();
    }
}
