<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\EarningController;
use App\Http\Controllers\Api\HelpController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\WebhookController;
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

        // Change Password
        Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

        // Driver Features
        Route::get('/driver/dashboard', [DriverController::class, 'dashboard']);
        Route::get('/driver/vehicle', [DriverController::class, 'vehicle']);
        Route::put('/driver/vehicle', [DriverController::class, 'updateVehicle']);
        Route::put('/driver/availability', [DriverController::class, 'updateAvailability']);
        Route::post('/jobs/{id}/complete', [DriverController::class, 'completeJob']);

        // Earnings
        Route::get('/driver/earnings', [EarningController::class, 'index']);

        // Notifications
        Route::get('/driver/notifications', [NotificationController::class, 'index']);
        Route::get('/driver/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/driver/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/driver/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::post('/driver/fcm-token', [NotificationController::class, 'registerFcmToken']);
        Route::delete('/driver/fcm-token', [NotificationController::class, 'removeFcmToken']);

        // Help Center
        Route::get('/help/faq', [HelpController::class, 'faq']);
        Route::get('/help/contact', [HelpController::class, 'contactInfo']);
        Route::post('/help/contact', [HelpController::class, 'submitContact']);

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

// ─────────────────────────────────────────────
// Xendit Webhooks (Public — no auth, verified by callback token)
// Base URL: https://siwride.com/api/webhooks/xendit/{product}
// ─────────────────────────────────────────────
Route::prefix('webhooks/xendit')->group(function () {
    Route::post('/invoice', [WebhookController::class, 'invoice'])
        ->name('webhooks.xendit.invoice');

    Route::post('/fva', [WebhookController::class, 'fva'])
        ->name('webhooks.xendit.fva');

    Route::post('/disbursement', [WebhookController::class, 'disbursement'])
        ->name('webhooks.xendit.disbursement');

    Route::post('/payout-link', [WebhookController::class, 'payoutLink'])
        ->name('webhooks.xendit.payout-link');

    Route::post('/retail-outlet', [WebhookController::class, 'retailOutlet'])
        ->name('webhooks.xendit.retail-outlet');

    Route::post('/cards', [WebhookController::class, 'cards'])
        ->name('webhooks.xendit.cards');

    Route::post('/direct-debit', [WebhookController::class, 'directDebit'])
        ->name('webhooks.xendit.direct-debit');

    Route::post('/xenplatform', [WebhookController::class, 'xenplatform'])
        ->name('webhooks.xendit.xenplatform');

    Route::post('/reports', [WebhookController::class, 'reports'])
        ->name('webhooks.xendit.reports');

    Route::post('/payment-request', [WebhookController::class, 'paymentRequest'])
        ->name('webhooks.xendit.payment-request');

    Route::post('/payment-method', [WebhookController::class, 'paymentMethod'])
        ->name('webhooks.xendit.payment-method');

    Route::post('/payment-token', [WebhookController::class, 'paymentToken'])
        ->name('webhooks.xendit.payment-token');

    Route::post('/ewallet', [WebhookController::class, 'ewallet'])
        ->name('webhooks.xendit.ewallet');

    Route::post('/recurring-payment', [WebhookController::class, 'recurringPayment'])
        ->name('webhooks.xendit.recurring-payment');

    Route::post('/paylater', [WebhookController::class, 'paylater'])
        ->name('webhooks.xendit.paylater');

    Route::post('/qr-code', [WebhookController::class, 'qrCode'])
        ->name('webhooks.xendit.qr-code');

    Route::post('/payment-session', [WebhookController::class, 'paymentSession'])
        ->name('webhooks.xendit.payment-session');

    Route::post('/bill-payments', [WebhookController::class, 'billPayments'])
        ->name('webhooks.xendit.bill-payments');

    Route::post('/payouts-v3', [WebhookController::class, 'payoutsV3'])
        ->name('webhooks.xendit.payouts-v3');

    Route::post('/payment-request-v3', [WebhookController::class, 'paymentRequestV3'])
        ->name('webhooks.xendit.payment-request-v3');

    // Deprecated — single catch-all endpoint (kept for backward compatibility)
    Route::post('/', [WebhookController::class, 'xendit'])
        ->name('webhooks.xendit');
});
