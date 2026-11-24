<?php

namespace App\Services;

class DokuSignature
{
    public function generate(string $clientId, string $requestId, string $timestamp, string $path, string $body, string $secretKey): string
    {
        $components = implode("\n", [
            'Client-Id:'.$clientId,
            'Request-Id:'.$requestId,
            'Request-Timestamp:'.$timestamp,
            'Request-Target:'.$path,
            'Digest:'.base64_encode(hash('sha256', $body, true)),
        ]);

        return 'HMACSHA256='.base64_encode(hash_hmac('sha256', $components, $secretKey, true));
    }
}
