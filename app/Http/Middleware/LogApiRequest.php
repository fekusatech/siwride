<?php

namespace App\Http\Middleware;

use App\Models\ApiLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        $user = $request->user();

        ApiLog::create([
            'method' => $request->method(),
            'path' => $request->path(),
            'request_headers' => is_array($request->headers->all()) ? json_encode($request->headers->all()) : $request->headers->all(),
            'request_body' => is_array($request->except(['password', 'token'])) ? json_encode($request->except(['password', 'token'])) : $request->except(['password', 'token']),
            'response_body' => $response->getContent(),
            'status_code' => $response->getStatusCode(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'user_role' => $user?->role,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'duration_ms' => $durationMs,
        ]);

        return $response;
    }
}
