<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - D'Jali Team Mobile API
|--------------------------------------------------------------------------
*/

// Public Auth Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Test endpoint
Route::get('/test', function () {
    return response()->json([
        'message' => 'D\'Jali Team API is running!',
        'timestamp' => now()->toISOString(),
    ]);
});
