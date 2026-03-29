<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Driver;
use App\Models\Vehicle;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // KPIs
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('date', $today)->count();
        $pendingClaims = Order::whereNull('driver_id')->where('status', 'pending')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('price');
        
        // Charts: 7 Day Revenue Trend
        $last7Days = collect(range(6, 0))->map(function ($days) {
            return Carbon::today()->subDays($days)->format('Y-m-d');
        });

        $revenueData = Order::where('status', 'completed')
            ->where('date', '>=', Carbon::today()->subDays(6))
            ->select(DB::raw('DATE(date) as date'), DB::raw('SUM(price) as total'))
            ->groupBy('date')
            ->pluck('total', 'date');

        $revenueTrend = $last7Days->map(function ($date) use ($revenueData) {
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'total' => (float) ($revenueData[$date] ?? 0)
            ];
        });

        // Charts: Status Distribution
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $statusDistribution = [
            'labels' => $statusCounts->keys()->toArray(),
            'series' => $statusCounts->values()->toArray(),
        ];

        // Lists: Recent Orders
        $recentOrders = Order::with(['driver', 'vehicle'])
            ->latest()
            ->take(5)
            ->get();

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
        ]);
    }
}
