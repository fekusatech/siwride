<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = request()->user();
        $today = Carbon::today();
        $driverId = null;

        if ($user->role === 'driver') {
            $driver = Driver::where('email', $user->email)->first();
            $driverId = $driver ? $driver->id : 0;
        }

        // KPIs
        $totalOrdersQuery = Order::query();
        $todayOrdersQuery = Order::whereDate('date', $today);
        $totalRevenueQuery = Order::where('status', 'completed');

        if ($driverId !== null) {
            $totalOrdersQuery->where('driver_id', $driverId);
            $todayOrdersQuery->where('driver_id', $driverId);
            $totalRevenueQuery->where('driver_id', $driverId);
        }

        $totalOrders = $totalOrdersQuery->count();
        $todayOrders = $todayOrdersQuery->count();
        $pendingClaims = Order::whereNull('driver_id')->where('status', 'pending')->count();
        $totalRevenue = $totalRevenueQuery->sum('price');

        // Charts: 7 Day Revenue Trend
        $last7Days = collect(range(6, 0))->map(function ($days) {
            return Carbon::today()->subDays($days)->format('Y-m-d');
        });

        $revenueDataQuery = Order::where('status', 'completed')
            ->where('date', '>=', Carbon::today()->subDays(6));

        if ($driverId !== null) {
            $revenueDataQuery->where('driver_id', $driverId);
        }

        $revenueData = $revenueDataQuery
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(price) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $revenueTrend = $last7Days->map(function ($date) use ($revenueData) {
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'total' => (float) ($revenueData[$date] ?? 0),
            ];
        });

        // Charts: Status Distribution
        $statusCountsQuery = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');

        if ($driverId !== null) {
            $statusCountsQuery->where('driver_id', $driverId);
        }

        $statusCounts = $statusCountsQuery->pluck('count', 'status');

        $statusDistribution = [
            'labels' => $statusCounts->keys()->toArray(),
            'series' => $statusCounts->values()->toArray(),
        ];

        // Lists: Recent Orders
        $recentOrdersQuery = Order::with(['driver', 'vehicle'])->latest();

        if ($driverId !== null) {
            $recentOrdersQuery->where('driver_id', $driverId);
        }

        $recentOrders = $recentOrdersQuery->take(5)->get();

        // Extra Stats
        $totalDrivers = Driver::count();
        $totalVehicles = Vehicle::count();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_orders' => $totalOrders,
                'today_orders' => $todayOrders,
                'pending_claims' => $pendingClaims,
                'total_revenue' => (float) $totalRevenue,
                'total_drivers' => $totalDrivers,
                'total_vehicles' => $totalVehicles,
            ],
            'charts' => [
                'revenue_trend' => $revenueTrend,
                'status_distribution' => $statusDistribution,
            ],
            'recent_orders' => $recentOrders,
            'user_role' => $user->role,
        ]);
    }
}
