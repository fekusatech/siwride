<?php

use App\Services\DokuSignature;

it('matches an independently calculated HMAC SHA256 signature vector', function () {
    $signature = (new DokuSignature)->generate('test-client', 'test-request', '2026-11-04T00:00:00Z', '/checkout/v1/payment', '{}', 'test-secret');

    expect($signature)->toBe('HMACSHA256=MWMOZf/W35+PrH+y/KdPxECKqa8xZr1FoCwCKn4mF3M=');
});

it('signs the exact body and target path', function () {
    $signer = new DokuSignature;
    $original = $signer->generate('client', 'request', 'timestamp', '/checkout/v1/payment', '{}', 'secret');

    expect($signer->generate('client', 'request', 'timestamp', '/checkout/v1/payment', '{ }', 'secret'))->not->toBe($original)
        ->and($signer->generate('client', 'request', 'timestamp', '/other', '{}', 'secret'))->not->toBe($original);
});
