<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        try {
            $user = $request->user();

            ApiLog::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'request_headers' => json_encode($request->headers->all()),
                'request_body' => json_encode($request->except(['password', 'token', 'current_password', 'new_password'])),
                'response_body' => $response->getContent(),
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
