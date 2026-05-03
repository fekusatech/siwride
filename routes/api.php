<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - D'Jali Team Mobile API v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public Auth Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Test endpoint - remove after debug
        Route::get('/test-auth', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'user' => $request->user(),
            ]);
        });

        // User Profile
        Route::get('/user', function (Request $request) {
            return response()->json([
                'status' => 'success',
                'data' => $request->user(),
            ]);
        });

        Route::put('/user', function (Request $request) {
            $request->validate([
                'firstname' => 'string|max:255',
                'lastname' => 'string|max:255',
                'phone' => 'string|max:50',
            ]);

            $request->user()->update($request->only(['firstname', 'lastname', 'phone']));

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
                'data' => $request->user(),
            ]);
        });

        Route::post('/user/photo', function (Request $request) {
            $request->validate([
                'photo' => 'required|image|max:2048',
            ]);

            $path = $request->file('photo')->store('profiles', 'public');

            $request->user()->update(['image' => $path]);

            return response()->json([
                'status' => 'success',
                'message' => 'Photo uploaded successfully.',
                'data' => [
                    'image' => $path,
                ],
            ]);
        });

        Route::post('/auth/logout', function (Request $request) {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logged out successfully.',
            ]);
        });

        Route::post('/auth/logout-all', function (Request $request) {
            $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'All tokens revoked.',
            ]);
        });
        // Job Management (Driver/Shared)
        Route::get('/jobs/shared', [JobController::class, 'shared']);
        Route::get('/jobs/my', [JobController::class, 'myRides']);
        Route::get('/jobs/{id}', [JobController::class, 'show']);
        Route::get('/jobs/{id}/history', [JobController::class, 'history']);
        Route::post('/jobs/{id}/take', [JobController::class, 'take']);
        Route::post('/jobs/{id}/claim', [JobController::class, 'claim']);
        Route::post('/jobs/{id}/accept-claim', [JobController::class, 'acceptClaim']);
        Route::post('/jobs/{id}/reject-claim', [JobController::class, 'rejectClaim']);
        Route::patch('/jobs/{id}/status', [JobController::class, 'updateStatus']);
        Route::post('/jobs/{id}/evidence', [JobController::class, 'uploadEvidence']);

        // Tracking (Driver)
        Route::post('/tracking/update', [TrackingController::class, 'update']);

        // Admin Only Routes
        Route::middleware(AdminMiddleware::class)->group(function () {
            // Job Management (Admin)
            Route::get('/jobs', [JobController::class, 'indexAll']);
            Route::post('/jobs', [JobController::class, 'store']);
            Route::post('/jobs/{id}/assign', [JobController::class, 'assign']);

            // Driver Management
            Route::get('/users/pending', [UserController::class, 'indexPending']);
            Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

            // Tracking (Admin)
            Route::get('/tracking/active', [TrackingController::class, 'indexActive']);

            // Reports
            Route::get('/reports/salary', [ReportController::class, 'salary']);

            // Settings
            Route::get('/settings', [SettingController::class, 'index']);
            Route::put('/settings', [SettingController::class, 'update']);
        });
    });
});
