<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    private const MAX_BODY_BYTES = 16384;

    private function sanitize(array $data): array
    {
        $clean = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && preg_match('/password|token|secret/i', $key)) {
                continue;
            }
            $clean[$key] = is_array($value) ? $this->sanitize($value) : $value;
        }

        return $clean;
    }

    private function body(array $data): string
    {
        $encoded = json_encode($this->sanitize($data), JSON_INVALID_UTF8_SUBSTITUTE);

        return strlen($encoded) > self::MAX_BODY_BYTES
            ? json_encode(['truncated' => true, 'bytes' => strlen($encoded)])
            : $encoded;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        try {
            $user = $request->user();
            $headers = $request->headers->all();
            foreach (['authorization', 'cookie', 'x-callback-token'] as $header) {
                unset($headers[$header]);
            }
            $isAuth = preg_match('~(?:^|/)(?:auth|login|register|logout|[^/]*password[^/]*)(?:/|$)~i', $request->path());
            $responseData = json_decode($response->getContent() ?: '', true);

            ApiLog::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'request_headers' => $this->body($headers),
                'request_body' => $this->body($request->all()),
                'response_body' => $isAuth ? null : $this->body(is_array($responseData) ? $responseData : []),
                'status_code' => $response->getStatusCode(),
                'user_id' => $user?->id,
                'user_email' => $user?->email,
                'user_role' => $user?->role,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log API request: '.$e->getMessage());
        }

        return $response;
    }
}
