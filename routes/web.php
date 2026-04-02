<?php

use App\Http\Controllers\Auth\RegisteredDriverController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/driver/register', [RegisteredDriverController::class, 'create'])->name('driver.register');
Route::post('/driver/register', [RegisteredDriverController::class, 'store']);

Route::get('/login-admin', function () {
    return Inertia::render('Admin/Login');
})->name('admin.login');

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\PublicClaimController;

Route::get('/c/{booking_code}', [PublicClaimController::class, 'show'])->name('orders.claim.show');
Route::post('/c/{booking_code}', [PublicClaimController::class, 'store'])->name('orders.claim.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('admin/profile', [ProfileController::class, 'edit'])->name('admin.profile');
    Route::put('admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

    Route::get('admin/orders/calendar', [OrderController::class, 'calendar'])->name('admin.orders.calendar');
    Route::get('admin/orders/import', [OrderController::class, 'importPage'])->name('admin.orders.import');
    Route::post('admin/orders/import', [OrderController::class, 'import'])->name('admin.orders.import.store');
    Route::get('admin/orders/import/template', [OrderController::class, 'downloadTemplate'])->name('admin.orders.import.template');
    Route::post('admin/orders/{order}/share', [OrderController::class, 'share'])->name('admin.orders.share');
    Route::patch('admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::resource('admin/orders', OrderController::class)->names('admin.orders');

    Route::resource('admin/drivers', DriverController::class)->names('admin.drivers');

    Route::resource('admin/vehicles', VehicleController::class)->names('admin.vehicles');

    Route::get('admin/settings/general', [SettingController::class, 'general'])->name('admin.settings.general');
    Route::post('admin/settings/general', [SettingController::class, 'updateGeneral'])->name('admin.settings.update-general');
});

require __DIR__.'/settings.php';
