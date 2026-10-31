<?php

use App\Http\Middleware\LogApiRequest;
use App\Models\ApiLog;
use Illuminate\Http\Request;

it('removes credentials recursively from logs and sensitive headers', function () {
    $request = Request::create('/api/orders', 'POST', [
        'safe' => 'visible', 'password_confirmation' => 'hidden',
        'nested' => ['access_token' => 'hidden', 'api_secret' => 'hidden', 'current_password' => 'hidden'],
    ], [], [], ['HTTP_AUTHORIZATION' => 'Bearer hidden', 'HTTP_COOKIE' => 'hidden', 'HTTP_X_CALLBACK_TOKEN' => 'hidden']);
    app(LogApiRequest::class)->handle($request, fn () => response()->json(['nested' => ['token' => 'hidden'], 'ok' => true]));
    $log = ApiLog::latest('id')->firstOrFail();
    expect($log->request_body)->toContain('visible')->not->toContain('hidden')
        ->and($log->request_headers)->not->toContain('hidden')
        ->and($log->response_body)->not->toContain('hidden');
});

it('does not persist authentication responses', function (string $path) {
    app(LogApiRequest::class)->handle(Request::create($path, 'POST'), fn () => response()->json(['credential' => 'hidden']));
    expect(ApiLog::latest('id')->firstOrFail()->response_body)->toBeNull();
})->with(['/api/login', '/api/register', '/api/customer/auth/login', '/api/auth/change-password']);

it('bounds log payloads and prunes only old logs', function () {
    app(LogApiRequest::class)->handle(Request::create('/api/orders', 'POST', ['large' => str_repeat('x', 20000)]), fn () => response()->json(['large' => str_repeat('x', 20000)]));
    $log = ApiLog::latest('id')->firstOrFail();
    expect(strlen($log->request_body))->toBeLessThanOrEqual(16384)
        ->and(json_decode($log->response_body, true)['truncated'])->toBeTrue();
    $old = $log->replicate();
    $old->created_at = now()->subDays(31);
    $old->save();
    $this->artisan('model:prune', ['--model' => [ApiLog::class]])->assertSuccessful();
    expect($log->fresh())->not->toBeNull()->and($old->fresh())->toBeNull();
});
