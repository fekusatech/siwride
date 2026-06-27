<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Order;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    private function getDriver(Request $request): ?Driver
    {
        return Driver::where('email', $request->user()->email)->first();
    }

    public function index(Request $request)
    {
        $driver = $this->getDriver($request);
        if (! $driver) {
            return response()->json(['status' => 'error', 'message' => 'Driver not found'], 404);
        }

        $period = $request->query('period', 'monthly');
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $query = Order::where('driver_id', $driver->id)
            ->where('status', 'selesai')
            ->where('is_cancelled', false);

        if ($period === 'daily') {
            $startDate = $request->query('date', now()->toDateString());
            $endDate = $startDate;
            $groupFormat = 'date';
            $labelFormat = 'Y-m-d';
        } elseif ($period === 'weekly') {
            $startOfWeek = now()->setISODate($year, now()->weekOfYear)->startOfWeek()->toDateString();
            $endOfWeek = now()->setISODate($year, now()->weekOfYear)->endOfWeek()->toDateString();
            $startDate = $request->query('start_date', $startOfWeek);
            $endDate = $request->query('end_date', $endOfWeek);
            $groupFormat = 'date';
            $labelFormat = 'Y-m-d';
        } else {
            $startDate = "{$year}-{$month}-01";
            $endDate = now()->parse($startDate)->endOfMonth()->toDateString();
            $groupFormat = 'date';
            $labelFormat = 'Y-m-d';
        }

        $orders = (clone $query)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->get();

        $totalEarnings = (float) $orders->sum('price');
        $totalJobs = $orders->count();
        $cashJobs = $orders->where('is_cash', true)->count();
        $nonCashJobs = $orders->where('is_cash', false)->count();

        $dailyBreakdown = $orders->groupBy('date')->map(function ($dayOrders, $date) {
            return [
                'date' => $date,
                'jobs' => $dayOrders->count(),
                'earnings' => (float) $dayOrders->sum('price'),
                'orders' => $dayOrders->map(function ($o) {
                    return [
                        'id' => $o->id,
                        'booking_code' => $o->booking_code,
                        'pickup' => $o->pickup_address,
                        'dropoff' => $o->dropoff_address,
                        'time' => $o->time,
                        'price' => (float) $o->price,
                        'is_cash' => $o->is_cash,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'summary' => [
                    'total_earnings' => $totalEarnings,
                    'total_jobs' => $totalJobs,
                    'cash_jobs' => $cashJobs,
                    'non_cash_jobs' => $nonCashJobs,
                ],
                'daily_breakdown' => $dailyBreakdown,
            ],
        ]);
    }
}
