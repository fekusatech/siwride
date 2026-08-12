<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverReferral;
use App\Models\DriverService;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('driver')->user();

        $serviceCounts = DriverService::where('driver_id', $user->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $referrals = DriverReferral::where('driver_id', $user->id);

        $orders = Order::where('driver_id', $user->driver_id)
            ->latest()
            ->paginate(10, pageName: 'orders_page');

        return Inertia::render('Driver/Dashboard', [
            'serviceCounts' => [
                'pending' => $serviceCounts['pending'] ?? 0,
                'approved' => $serviceCounts['approved'] ?? 0,
                'rejected' => $serviceCounts['rejected'] ?? 0,
            ],
            'earnings' => [
                'pending' => (clone $referrals)->where('status', 'pending')->sum('commission_amount'),
                'paid' => (clone $referrals)->where('status', 'paid')->sum('commission_amount'),
            ],
            'orders' => $orders,
        ]);
    }
}
