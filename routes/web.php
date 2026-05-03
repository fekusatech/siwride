<?php

use App\Http\Controllers\Auth\RegisteredDriverController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/dokumentasi', function () {
    return view('api-docs');
})->name('api.docs');

Route::get('/driver/register', [RegisteredDriverController::class, 'create'])->name('driver.register');
Route::post('/driver/register', [RegisteredDriverController::class, 'store']);

// Route::get('/login', function () {
//     return Inertia::render('Admin/Login');
// })->name('login');

Route::get('/login-admin', function () {
    return Inertia::render('Admin/Login');
})->name('admin.login');

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\LocationSearchController;
use App\Http\Controllers\PublicClaimController;

Route::get('/c/{booking_code}', [PublicClaimController::class, 'show'])->name('orders.claim.show');
Route::post('/c/{booking_code}', [PublicClaimController::class, 'store'])->name('orders.claim.store');

Route::inertia('/about', 'customer/about')->name('about');
Route::inertia('/services', 'customer/services')->name('services');
Route::inertia('/area-coverage', 'customer/area-coverage')->name('area-coverage');
Route::inertia('/vehicles', 'customer/vehicles')->name('vehicles');
Route::inertia('/vehicles/{slug}', 'customer/vehicles/[slug]')->name('vehicles.slug');
Route::inertia('/testimonials', 'customer/testimonials')->name('testimonials');
Route::inertia('/faq', 'customer/faq')->name('faq');
Route::inertia('/terms', 'customer/terms')->name('terms');
Route::inertia('/privacy', 'customer/privacy')->name('privacy');
// Booking route moved outside auth middleware
Route::inertia('/contact', 'customer/contact')->name('contact');
// Public Customer Booking Routes (No Auth Required)
Route::middleware('guest:customer')->group(function () {
    Route::get('/customer/login', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/customer/login', [CustomerAuthController::class, 'login']);
    Route::get('/customer/register', [CustomerAuthController::class, 'showRegisterForm'])->name('customer.register');
    Route::post('/customer/register', [CustomerAuthController::class, 'register']);
});

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CustomerProfileController;

Route::middleware('auth:customer')->group(function () {
    Route::get('/customer/profile', [CustomerProfileController::class, 'index'])->name('customer.profile');
    Route::put('/customer/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
});

Route::get('/locations/search', [LocationSearchController::class, 'search'])->name('locations.search');
Route::get('/booking', [CustomerOrderController::class, 'index'])->name('booking');
Route::post('/booking/validate-email', [CustomerOrderController::class, 'validateEmail'])->name('booking.validate-email');
Route::get('/booking/search', [CustomerOrderController::class, 'searchBookings'])->name('booking.search');
Route::post('/orders', [CustomerOrderController::class, 'store'])->name('orders.store');
Route::get('/booking/success', [CustomerOrderController::class, 'success'])->name('booking.success');
Route::get('/booking/{booking_code}', [CustomerOrderController::class, 'show'])->name('booking.show');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('admin/profile', [ProfileController::class, 'edit'])->name('admin.profile');
    Route::put('admin/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::put('admin/profile/password', [ProfileController::class, 'updatePassword'])->name('admin.profile.password');

    Route::get('admin/orders/calendar', [OrderController::class, 'calendar'])->name('admin.orders.calendar');
    Route::get('admin/orders/import', [OrderController::class, 'importPage'])->name('admin.orders.import');
    Route::post('admin/orders/import', [OrderController::class, 'import'])->name('admin.orders.import.store');
    Route::get('admin/orders/import/template', [OrderController::class, 'downloadTemplate'])->name('admin.orders.import.template');
    Route::post('admin/orders/distance', [OrderController::class, 'distanceApi'])->name('admin.orders.distance');
    Route::post('admin/orders/{order}/share', [OrderController::class, 'share'])->name('admin.orders.share');
    Route::patch('admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.status');
    Route::post('admin/orders/{order}/accept-claim', [OrderController::class, 'acceptClaim'])->name('admin.orders.accept-claim');
    Route::post('admin/orders/{order}/reject-claim', [OrderController::class, 'rejectClaim'])->name('admin.orders.reject-claim');
    Route::post('admin/orders/{order}/resend-wa', [OrderController::class, 'resendWaToDriver'])->name('admin.orders.resend-wa');
    Route::post('admin/orders/{order}/take', [OrderController::class, 'take'])->name('admin.orders.take');
    Route::resource('admin/orders', OrderController::class)->names('admin.orders');

    Route::post('admin/drivers/{driver}/sync-user', [DriverController::class, 'syncUser'])->name('admin.drivers.sync-user');
    Route::resource('admin/drivers', DriverController::class)->names('admin.drivers');

    Route::resource('admin/vehicles', VehicleController::class)->names('admin.vehicles');

    Route::post('admin/zones/validate', [ZoneController::class, 'validatePoint'])->name('admin.zones.validate');
    Route::resource('admin/zones', ZoneController::class)->names('admin.zones');

    Route::get('admin/settings/general', [SettingController::class, 'general'])->name('admin.settings.general');
    Route::post('admin/settings/general', [SettingController::class, 'updateGeneral'])->name('admin.settings.update-general');

    Route::resource('admin/users', UserController::class)->names('admin.users');
});

require __DIR__.'/settings.php';
